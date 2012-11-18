<?php
class ApplicationController extends ActionController_Base
{
    public function __call($method, $params)
    {
        if (preg_match("/^(\w+)_only$/", $method, $m)) {
            if (current_user()->{'is_' . $m[1] . '_or_higher'}())
                return true;
            else {
                $this->_access_denied();
                return false;
            }
        }
        
        # For many actions, GET invokes the HTML UI, and a POST actually invokes
        # the action, so we often want to require higher access for POST (so the UI
        # can invoke the login dialog).
        elseif (preg_match("/^post_(\w+)_only$/", $method, $m)) {
            if (!$this->request()->post())
                return true;
            elseif (current_user()->{'is_' . $m[1] . '_or_higher'}())
                return true;
            else {
                $this->_access_denied();
                return false;
            }
        }
        
        Rails::raise('ActionController_Exception', "Unknown method 'ApplicationController::%s'", $method);
    }
    
    /**
     * This is found in SessionHelper in MoeBooru
     */
    public function page_number()
    {
        if (!isset($this->page_number))
            $this->page_number = $this->params()->page ?: 1;
        return $this->page_number;
    }
    
    # LoginSystem {
    protected function _access_denied()
    {
        $previous_url = $this->params()->url || $this->request()->fullpath();
        
        $this->_respond_to([
            'html' => function()use($previous_url) {
                $this->_notice('Access denied');
                $this->_redirect_to("user#login", array('url' => $previous_url));
            },
            'xml'  => function() {
                $this->_render(array('xml' => array('success' => false, 'reason' => "access denied"), 'root' => "response"), array('status' => 403));
            },
            'json' => function() {
                $this->_render(array('json' => array('success' => false, 'reason' => "access denied")), array('status' => 403));
            }
        ]);
    }

    protected function _set_current_user()
    {
        $user = null;
        $AnonymousUser = array(
            'id'                       => 0,
            'level'                    => 0,
            'name'                     => "Anonymous",
            'pretty_name'              => "Anonymous",
            'is_anonymous'             => true,
            'show_samples'             => true,
            'has_avatar'               => false,
            'language'                 => '',
            'secondary_languages'      => '',
            'secondary_language_array' => array(),
            'pool_browse_mode'         => 1,
            'always_resize_images'     => true,
            'ip_addr'                  => $this->request()->remote_ip()
        );
        
        if (!empty($_SESSION['user_id']))
            $user = User::find($_SESSION['user_id']);
        elseif (isset($_COOKIE['login']) && isset($_COOKIE['pass_hash']))
            $user = User::authenticate_hash($_COOKIE['login'], $_COOKIE['pass_hash']);
        elseif (isset($this->params()->login) && isset($this->params()->password_hash))
            $user = User::authenticate($this->params()->login, $this->params()->password_hash);
        elseif (isset($this->params()->user['name']) && isset($this->params()->user['password']))
            $user = User::authenticate($this->params()->user['name'], $this->params()->user['password']);
        if ($user) {
            if ($user->is_blocked() && $user->ban && $user->ban->expires_at < gmd()) {
                $user->update_attribute('level', CONFIG()->starting_level);
                Ban::destroy_all("user_id = ".$user->id);
            }
        } else
            $user = new User($AnonymousUser);
        
        User::set_current_user($user);
        $this->current_user = $user;
        
        // ActiveRecord_Base::init_history();
        
        if (!current_user()->is_anonymous)
            current_user()->log($this->request()->remote_ip());
    }

    # iTODO:
    protected function set_country()
    {
        current_user()->country = '--';
        // current_user()->country = Rails::cache()->fetch(['type' => 'geoip', 'ip' => $this->request()->remote_ip()], ['expires_in' => '+1 month']) do
            // begin
                // GeoIP->new(Rails.root.join('db', 'GeoIP.dat').to_s).country($this->request()->remote_ip()).country_code2
            // rescue
                // '--'
            // end
        // end
    }
    
    # } RespondToHelpers {
    
    protected function _respond_to_success($notice, $redirect_to_params, array $options = array())
    {
        $extra_api_params = isset($options['api']) ? $options['api'] : array();

        $this->_respond_to(array(
            'html' => function() use ($notice, $redirect_to_params) {
                $this->_notice($notice);
                $this->_redirect_to($redirect_to_params);
            },
            'json' => function() use ($extra_api_params) {
                $this->_render(array('json' => array_merge($extra_api_params, array('success' => true))));
            },
            'xml' => function() use ($extra_api_params) {
                $this->_render(array('xml' => array_merge($extra_api_params, array('success' => true)), 'root' => "response"));
            }
        ));
    }

    protected function _respond_to_error($obj, $redirect_to_params, $options = array())
    {
        !is_array($redirect_to_params) && $redirect_to_params = array($redirect_to_params);
        $extra_api_params = isset($options['api']) ? $options['api'] : array();
        $status = isset($options['status']) ? $options['status'] : 500;

        if ($obj instanceof ActiveRecord_Base) {
            $obj = $obj->errors()->full_messages(", ");
            $status = 420;
        }
        
        if ($status == 420)
            $status = "420 Invalid Record";
        elseif ($status == 421)
            $status = "421 User Throttled";
        elseif ($status == 422)
            $status = "422 Locked";
        elseif ($status == 423)
            $status = "423 Already Exists";
        elseif ($status == 424)
            $status = "424 Invalid Parameters";

        $this->_respond_to(array(
            'html' => function()use($obj, $redirect_to_params) {
                $this->_notice("Error: " . $obj);
                $this->_redirect_to($redirect_to_params);
            },
            
            'json' => function()use($obj, $extra_api_params, $status) {
                $this->_render(array('json' => array_merge($extra_api_params, array('success' => false, 'reason' => $obj))), array('status' => $status));
            },
            
            'xml' => function()use($obj, $extra_api_params, $status) {
                $this->_render(array('xml' => array_merge($extra_api_params, array('success' => false, 'reason' => $obj)), array('root' => "response")), array('status' => $status));
            }
        ));
    }

    protected function _respond_to_list($inst_var_name, array $formats = array())
    {
        $inst_var = $this->$inst_var_name;
        
        $this->_respond_to(array(
            'html',
            isset($formats['atom']) ? 'atom' : null,
            'json' => function() use ($inst_var) {
                $this->_render(array('json' => $inst_var->to_json()));
            },
            'xml'  => function() use ($inst_var) {
                $this->_render(array('xml' => $inst_var->to_xml(array('root' => $inst_var_name))));
            }
        ));
    }

    protected function _render_error($record)
    {
        $this->record = $record;
        $this->_render(['inline' => "<?= \$this->error_messages_for('record') ?>", 'layout' => "bare"], ['status' => 500]);
    }
    # }
    
  // protected :build_cache_key
  // protected :get_cache_key
    
    # iTODO:
    public function get_ip_ban()
    {
        // $ban = IpBans::find_first(['conditions' => ["? <<= ip_addr", $this->request()->remote_ip()]]);
        // return $ban ?: null;
    }
    
    protected function check_ip_ban()
    {
         if ($this->request()->params()->controller == "banned" and $this->request()->params()->action == "index") {
            return;
        }
        
        $ban = $this->get_ip_ban();
        if (!$ban) {
            return;
        }

        if ($ban->expires_at && $ban->expires_at < gmd()) {
            IpBans::destroy_all("ip_addr = '{$this->request()->remote_ip()}'");
            return;
        }

        $this->_redirect_to('banned#index');
    }

    protected function save_tags_to_cookie()
    {
        if ($this->params()->tags || ($this->params()->post && $this->params()->post['tags'])) {
            $post_tags = isset($this->params()->post['tags']) ? (string)$this->params()->post['tags'] : '';
            $tags = TagAlias::to_aliased(preg_split('/\S+/', (strtolower($this->params()->tags ?: $post_tags))));
            if ($recent_tags = $this->cookies("recent_tags"))
                $tags = array_merge($tags, preg_split('/\S+/', $recent_tags));
            $this->cookies("recent_tags", implode(" ", array_slice($tags, 0, 20)));
        }
    }

    public function set_cache_headers()
    {
        $this->response->headers["Cache-Control"] = "max-age=300";
    }

    # iTODO:
    public function cache_action()
    {
        // if ($this->request()->method() == 'get' && !preg_match('/Googlebot/', $this->request()->env()) && $this->params()->format != "xml" && $this->params()->format != "json") {
            // list($key, $expiry) = $this->get_cache_key($this->controller_name(), $this->action_name(), $this->params(), 'user' => current_user());

            // if ($key && count($key) < 200) {
                // $cached = Rails::cache()->read($key);

                // if ($cached) {
                    // $this->render(['text' => $cached, 'layout' => false]);
                    // return;
                // }
            // }

            // $this->yield();

            // if ($key && strpos($this->response->headers['Status'], '200') === 0) {
                // Rails::cache()->write($key, $this->response->body, ['expires_in' => $expiry]);
            // }
        // } else {
            // $this->yield();
        // }
    }

    protected function _init_cookies()
    {
        if ($this->params()->format == "xml" || $this->params()->format == "json")
            return;

        $forum_posts = ForumPost::find_all(['order' => "updated_at DESC", 'limit' => 10, 'conditions' => "parent_id IS NULL"]);
        $this->cookies("current_forum_posts", json_encode(array_map(function($fp) {
            if (current_user()->is_anonymous) {
                $updated = false;
            } else {
                $updated = $fp->updated_at > current_user()->last_forum_topic_read_at;
            }
            return [$fp->title, $fp->id, $updated, ceil($fp->response_count / 30.0)];
        }, $forum_posts->all())));

        $this->cookies("country", current_user()->country);

        if (!current_user()->is_anonymous) {
            $this->cookies()->add("user_id", (string)current_user()->id);
            
            $this->cookies()->add("user_info", current_user()->user_info_cookie());

            $this->cookies()->add("has_mail", (current_user()->has_mail ? "1" : "0"));
            
            $this->cookies()->add("forum_updated", (current_user()->is_privileged_or_higher() && ForumPost::updated(current_user()) ? "1" : "0"));
            
            $this->cookies()->add("comments_updated", (current_user()->is_privileged_or_higher() && Comment::updated(current_user()) ? "1" : "0"));
            
            if (current_user()->is_janitor_or_higher()) {
                $mod_pending = Post::count(array('conditions' => array("status = 'flagged' OR status = 'pending'")));
                $this->cookies()->add("mod_pending", (string)$mod_pending);
            }

            if (current_user()->is_blocked()) {
                if (current_user()->ban)
                    $this->cookies()->add("block_reason", "You have been blocked. Reason: ".current_user()->ban->reason.". Expires: ".substr(current_user()->ban->expires_at, 0, 10));
                else
                    $this->cookies()->add("block_reason", "You have been blocked.");
            } else
                $this->cookies()->add("block_reason", "");
// vde(json_encode(current_user()->blacklisted_tags_array()));
            $this->cookies()->add("resize_image", (current_user()->always_resize_images ? "1" : "0"));

            $this->cookies()->add('show_advanced_editing', (current_user()->show_advanced_editing ? "1" : "0"));
            $this->cookies()->add("my_tags", current_user()->my_tags);
            $this->cookies()->add("blacklisted_tags", json_encode(current_user()->blacklisted_tags_array()));
            $this->cookies()->add("held_post_count", (string)current_user()->held_post_count());
        } else {
            $this->cookies()->delete('user_info');
            $this->cookies()->delete('login');
            $this->cookies("blacklisted_tags", json_encode(CONFIG()->default_blacklists));
        }
    }
    
    protected function _set_title($title = null)
    {
        if (!$title)
            $title = CONFIG()->app_name;
        else
            $title .= ' | ' . CONFIG()->app_name;
        $this->page_title = $title;
    }
    
    protected function _notice($str)
    {
        $this->cookies()->add('notice', $str);
    }
    
    protected function _set_locale()
    {
        if ($this->params()->locale and in_array($this->params()->locale, CONFIG()->available_locales)) {
            $this->cookies('locale', $this->params()->locale, array('expires' => time() + 365*24*60*60));
            $this->I18n()->locale($this->params()->locale);
        } elseif ($this->cookies()->locale and in_array($this->cookies()->locale, CONFIG()->available_locales)) {
            $this->I18n()->locale($this->cookies()->locale);
        }
    }

    protected function _sanitize_params()
    {
        if ($this->params()->page) {
            if ($this->params()->page < 1)
                $this->params()->page = 1;
        } else
            $this->params('page', 1);
    }

    protected function admin_only()
    {
        if (!current_user()->is_admin())
            $this->_access_denied();
    }
    
    protected function member_only()
    {
        if (!current_user()->is_member_or_higher())
            $this->_access_denied();
    }
    
    protected function post_privileged_only()
    {
        if (!current_user()->is_privileged_or_higher())
            $this->_access_denied();
    }
    
    protected function post_member_only()
    {
        if (!current_user()->is_member_or_higher())
            $this->_access_denied();
    }
    
    protected function no_anonymous()
    {
        if (current_user()->is_anonymous())
            $this->_access_denied();
    }

    protected function sanitize_id()
    {
        $this->params()->id = (int)$this->params()->id;
    }
    
    # iTODO:
    protected function _filters()
    {
        return [
            'before' => [
                '_set_current_user',
                'set_country',
                '_set_locale',
                '_set_title',
                '_sanitize_params'
                //'check_ip_ban'
            ],
            'after' => ['_init_cookies']
        ];
    }
}