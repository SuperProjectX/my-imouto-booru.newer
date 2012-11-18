<?php
ActiveRecord::load_model(['Ban', 'Tag', 'UserBlacklistedTag']);
class User_AlreadyFavoritedError extends Exception{}
class User_NoInvites extends Exception{}
class User_HasNegativeRecord extends Exception{}

class User extends ActiveRecord_Base
{
    static private $_current;
    
    public $current_email, $country;
    
    static public function set_current_user(User $user)
    {
        self::$_current = $user;
    }
    
    static public function current()
    {
        return self::$_current;
    }
    
    # Defines various convenience methods for finding out the user's level
    public function __call($method, $params)
    {
        if (strpos($method, 'is_post_')) {
            return $this->_parse_is_post_level_or($method);
        } elseif (strpos($method, 'is_') === 0) {
            if (is_int(strpos($method, '_or_')))
                return $this->_parse_is_level_or($method);
            else
                return $this->_parse_is_level($method);
        }
        parent::__call($method, $params);
    }
    
    public function log($ip)
    {
        // Rails.cache.fetch({ 'type' => :user_logs, 'id' => self.id, 'ip' => ip }, 'expires_in' => 10.minutes) do
            // Rails.cache.fetch({ 'type' => :user_logs, 'id' => :all }, 'expires_id' => 1.day) do
                // UserLog.where('created_at < ?', 3.days.ago).delete_all
            // end
            // begin
                // log_entry = self.user_logs.find_or_initialize_by_ip_addr('ip_addr' => ip)
                // log_entry.created_at = Time.now
                // log_entry.save
            // # Once in a blue moon there will be race condition on find_or_initialize
            // # resulting unique key constraint violation.
            // # It doesn't really affect anything so just ignore that error.
            // rescue ActiveRecord::RecordNotUnique
                // true
            // end
        // end
    }

    # UserBlacklistMethods {
    # TODO: I don't see the advantage of normalizing these. Since commas are illegal
    # characters in tags, they can be used to separate lines (with whitespace separating
    # tags). Denormalizing this into a field in users would save a SQL query.
    protected function _on_blacklisted_tags_change($blacklists)
    {
        $this->blacklisted_tags = $blacklists;
    }

    public function blacklisted_tags()
    {
        return implode("\n", $this->blacklisted_tags_array()) . "\n";
    }

    public function blacklisted_tags_array()
    {
        if ($this->user_blacklisted_tag)
            return preg_split("/(\r\n|\r|\n)/", trim($this->user_blacklisted_tag->tags));
        else
            return [];
    }

    protected function _commit_blacklists() 
    {
        if ($this->user_blacklisted_tag && isset($this->blacklisted_tags))
            $this->user_blacklisted_tag->update_attribute('tags', $this->blacklisted_tags);
    }

    protected function _set_default_blacklisted_tags()
    {
        UserBlacklistedTag::create(array('user_id' => $this->id, 'tags' => implode("\r\n", CONFIG()->default_blacklists)));
    }
    
    # } UserAuthenticationMethods {
    
    static public function authenticate($name, $pass)
    {
        return self::authenticate_hash($name, self::_sha1($pass));
    }

    static public function authenticate_hash($name, $pass)
    {
        $user = parent::find_first(array('conditions' => array("lower(name) = lower(?) AND password_hash = ?", $name, $pass)));
        return $user;
    }

    static protected function _sha1($pass)
    {
        return sha1(CONFIG()->user_password_salt . '--' . $pass . '--');
    }
    
    # } UserPasswordMethods {
    
    public $password, $current_password;
    
    protected function _validate_current_password()
    {
        # First test to see if it's creating new user (no password_hash)
        # or updating user. The second is to see if the action involves
        # updating password (which requires this validation).
        if ($this->password_hash and ($this->password or ($this->_email_changed() or $this->current_email))) {
            if (!$this->current_password)
                $this->errors()->add('current_password', 'blank');
            elseif (!User::authenticate($this->name, $this->current_password))
                $this->errors()->add('current_password', 'invalid');
        }
    }
    
    protected function _encrypt_password()
    {
        if ($this->password)
            $this->password_hash = self::_sha1($this->password);
    }

    public function reset_password()
    {
        $consonants = "bcdfghjklmnpqrstvqxyz";
        $vowels = "aeiou";
        $pass = "";

        foreach (range(1, 4) as $i) {
            $pass .= substr($consonants, rand(0, 20), 1);
            $pass .= substr($vowels, rand(0, 4), 1);
        }

        $pass .= rand(0, 100);
        self::execute_sql("UPDATE users SET password_hash = ? WHERE id = ?", self::sha1($pass), $this->id);
        return $pass;
    }
    
    # } UserCountMethods {
    
    # TODO: This isn't used anymore. Should be safe to delete.
    static public function fast_count()
    {
        return self::select_value("SELECT row_count FROM table_data WHERE name = 'users'");
    }

    protected function _increment_count()
    {
        self::execute_sql("UPDATE table_data set row_count = row_count + 1 where name = 'users'");
    }
    
    protected function _decrement_count()
    {
        self::execute_sql("UPDATE table_data set row_count = row_count - 1 where name = 'users'");
    }
    
    # } UserNameMethods {
    
    static private function _find_name_helper($user_id)
    {
        if (!$user_id)
          return CONFIG()->default_guest_name;

        $user = self::find_first(['conditions' => ['id = ?', $user_id]]);

        if ($user) {
          return $user->name;
        } else {
          return CONFIG()->default_guest_name;
        }
    }

    static public function find_name($user_id)
    {
        # iTODO:
        // return Rails.cache.fetch("user_name:#{user_id}") do
        return self::_find_name_helper($user_id);
        // end
    }

    static public function find_by_name($name)
    {
        return self::find_first(['conditions' => ["lower(name) = lower(?)", $name]]);
    }
    
    public function pretty_name()
    {
        return str_replace('_', ' ', $this->name);
    }

    # iTODO:
    protected function _update_cached_name()
    {
        // Rails::cache()->write("user_name:".$this->id, $this->name);
    }
    # }

    # UserApiMethods {
    # iTODO:
    public function to_xml(array $options = array())
    {
        // !isset($options['indent']) && $options['indent'] = 2;
        // if (isset($options['builder']))
            // $xml = $options['builder'];
        // else
            // $xml = Builder::XmlMarkup.new('indent' => options[:indent])
        
        // xml.post('name' => name, 'id' => id) do
            // blacklisted_tags_array.each do |t|
                // xml.blacklisted_tag('tag' => t)
            // end

            // yield options[:builder] if block_given?
        // end
    }

    public function as_json(array $args = array())
    {
        return to_json(['name' => $this->name, 'blacklisted_tags' => $this->blacklisted_tags_array(), 'id' => $this->id]);
    }

    public function user_info_cookie()
    {
        return implode(';', [$this->id, $this->level, ($this->use_browser ? "1":"0")]);
    }
    # }

    public function find_by_name_nocase($name)
    {
        return User::find_first(['conditions' => ["lower(name) = lower(?)", $name]]);
    }

    # UserTagMethods {
    # iTODO:
    public function uploaded_tags(array $options = array())
    {
        $type = !empty($options['type']) ? $options['type'] : null;
        
        // uploaded_tags = Rails.cache.read("uploaded_tags/#{id}/#{type}")
        // return uploaded_tags unless uploaded_tags == nil

        // if ((Rails.env == "test") == "test") {
            // # disable filtering in test mode to simplify tests
            // popular_tags = ""
        // } else {
            $popular_tags = implode(', ', self::select_values("SELECT id FROM tags WHERE tag_type = " . CONFIG()->tag_types['General'] . " ORDER BY post_count DESC LIMIT 8"));
            if ($popular_tags)
                $popular_tags = "AND pt.tag_id NOT IN (${popular_tags})";
        // }

        if ($type) {
            $type = (int)$type;
            $sql = "SELECT 
                (SELECT name FROM tags WHERE id = pt.tag_id) AS tag, COUNT(*) AS count
                FROM posts_tags pt, tags t, posts p
                WHERE p.user_id = {$this->id}
                AND p.id = pt.post_id
                AND pt.tag_id = t.id
                {$popular_tags}
                AND t.tag_type = {$type}
                GROUP BY pt.tag_id
                ORDER BY count DESC
                LIMIT 6
            ";
        } else {
            $sql = "SELECT 
                (SELECT name FROM tags WHERE id = pt.tag_id) AS tag, COUNT(*) AS count
                FROM posts_tags pt, posts p
                WHERE p.user_id = {$this->id}
                AND p.id = pt.post_id
                ${popular_tags}
                GROUP BY pt.tag_id
                ORDER BY count DESC
                LIMIT 6
            ";
        }

        $uploaded_tags = self::select($sql);

        // Rails.cache.write("uploaded_tags/#{id}/#{type}", uploaded_tags, 'expires_in' => 1.day)

        return $uploaded_tags;
    }

    # iTODO:
    public function voted_tags(array $options = array())
    {
        $type = !empty($options['type']) ? $options['type'] : null;

        // $favorite_tags = Rails.cache.read("favorite_tags/#{id}/#{type}")
        // if ($favorite_tags != nil)
            // return $favorite_tags;

        // if (Rails.env == "test") {
            // # disable filtering in test mode to simplify tests
            // popular_tags = ""
        // } else {
            $popular_tags = implode(', ', self::select_values("SELECT id FROM tags WHERE tag_type = " . CONFIG()->tag_types['General'] . " ORDER BY post_count DESC LIMIT 8"));
            if ($popular_tags)
                $popular_tags = "AND pt.tag_id NOT IN (${popular_tags})";
        // }

        if ($type) {
            $type = (int)$type;
            $sql = "SELECT
                (SELECT name FROM tags WHERE id = pt.tag_id) AS tag, SUM(v.score) AS sum
                FROM posts_tags pt, tags t, post_votes v
                WHERE v.user_id = {$this->id}
                AND v.post_id = pt.post_id
                AND pt.tag_id = t.id
                {$popular_tags}
                AND t.tag_type = {$type}
                GROUP BY pt.tag_id
                ORDER BY sum DESC
                LIMIT 6
            ";
        } else {
            $sql = "SELECT
                (SELECT name FROM tags WHERE id = pt.tag_id) AS tag, SUM(v.score) AS sum
                FROM posts_tags pt, post_votes v
                WHERE v.user_id = {$this->id}
                AND v.post_id = pt.post_id
                ${popular_tags}
                GROUP BY pt.tag_id
                ORDER BY sum DESC
                LIMIT 6
            ";
        }

        $favorite_tags = self::select($sql);

        // Rails.cache.write("favorite_tags/#{id}/#{type}", favorite_tags, 'expires_in' => 1.day)

        return $favorite_tags;
    }
    # }

    # UserPostMethods {
    public function recent_uploaded_posts()
    {
        $posts = Post::find_by_sql("SELECT p.* FROM posts p WHERE p.user_id = {$this->id} AND p.status <> 'deleted' ORDER BY p.id DESC LIMIT 6");
        return $posts ?: new ActiveRecord_Collection();
    }

    public function recent_favorite_posts()
    {
      return Post::find_all(['joins' => 'JOIN post_votes pv ON posts.id = pv.post_id', 'conditions' => ['pv.post_id = ? AND pv.score = ?', $this->id, 3], 'order' => 'posts.id', 'limit' => 6]);
    }

    public function favorite_post_count($options = array())
    {
        return self::select_value("SELECT COUNT(*) FROM post_votes v WHERE v.user_id = {$this->id} AND v.score = 3");
    }

    public function post_count()
    {
        if (!$this->post_count)
            $this->post_count = Post::count(['conditions' => ["user_id = ? AND status = 'active'", $this->id]]);
        return $this->post_count;
    }

    # iTODO:
    public function held_post_count()
    {
        return Post::count(['conditions' => ["user_id = ? AND is_held AND status <> 'deleted'", $this->id]]);
        // $version = (int)Rails::cache()->read("%cache_version");
        // $key = "held-post-count/v=".$version."/u=".$this->id;

        // return Rails::cache()->fetch($key) {
            // Post::count(['conditions' => ["user_id = ? AND is_held AND status <> 'deleted'", $this->id]]);
        // }
    }
    # }

    # UserLevelMethods {
    public function pretty_level()
    {
        return array_search($this->level, CONFIG()->user_levels);
    }

    protected function _set_role()
    {
        if (CONFIG()->enable_account_email_activation)
            $this->level = CONFIG()->user_levels["Unactivated"];
        else
            $this->level = CONFIG()->starting_level;

        $this->last_logged_in_at = gmd();
    }

    public function has_permission(ActiveRecord_Base $record, $foreign_key = 'user_id') 
    {
        return ($this->is_mod_or_higher() || $record->$foreign_key == $this->id);
    }

    # Return true if this user can change the specified attribute.
    #
    # If record is an ActiveRecord object, return;s true if the change is allowed to complete.
    #
    # If record is an ActiveRecord class (eg. Pool rather than an actual pool), return;s
    # false if the user would never be allowed to make this change for any instance of the
    # object, and so the option should not be presented.
    #
    # For example, can_change(Pool, :description) return;s true (unless the user level
    # is too low to change any pools), but can_change(Pool.find(1), :description) return;s
    # false if that specific pool is locked.
    #
    # attribute usually corresponds with an actual attribute in the class, but any value
    # can be used.
    public function can_change(ActiveRecord_Base $record, $attribute)
    {
        $method = "can_change_" . $attribute;
        if ($this->is_mod_or_higher())
            return true;
        elseif (method_exists($record, $method))
            return $record->$method($this);
        elseif (method_exists($record, 'can_change'))
            $record->can_change($this, $attribute);
        else
            return true;
    }

    static public function get_user_level($level)
    {
        static $user_level = [];
        
        if (!$user_level) {
            foreach (CONFIG()->user_levels as $name => $value) {
                $normalized_name = strtolower(str_replace(' ', '_', $name));
                $user_level[$normalized_name] = $value;
            }
        }
        
        return $user_level[$level];
    }
    # }

    # module UserInviteMethods {
    public function invite($name, $level)
    {
        if ($invite_count <= 0) {
            throw new User_NoInvites();
        }

        if ((int)$this->level >= CONFIG()->user_levels["Contributor"])
        {
            $this->level = CONFIG()->user_levels["Contributor"];
        }

        $invitee = User::find_by_name($name);

        if (!$invitee) {
            throw ActiveRecord::RecordNotFound();
        }

        if (UserRecord::exists(["user_id = ? AND is_positive = false AND reported_by IN (SELECT id FROM users WHERE level >= ?)", $invitee->id, CONFIG()->user_levels["Mod"]]) && !$this->is_admin()) {
            throw new User_HasNegativeRecord();
        }

        // transaction do
        if ($this->level == CONFIG()->user_levels["Contributor"]) {
            Post::find_all(['conditions' => ["user_id = ? AND status = 'pending'", $this->id]])->each(function($post) {
                $post->approve($id);
            });
        }
        $invitee->level = $level;
        $invitee->invited_by = $id;
        $invitee->save();
        # iTODO: add support for this
        // decrement! :invite_count
        self::execute_sql("UPDATE users SET invite_count = invite_count - 1 WHERE id = ".$this->id);
        $this->invite_count--;
        // end
    }
    # }

    # UserAvatarMethods {
    # post_id is being destroyed.  Clear avatar_post_ids for this post, so we won't use
    # avatars from this post.  We don't need to actually delete the image.
    static public function clear_avatars($post_id)
    {
        self::execute_sql("UPDATE users SET avatar_post_id = NULL WHERE avatar_post_id = ?", $post_id);
    }

    public function avatar_url()
    {
        return CONFIG()->url_base . "/data/avatars/".$this->id.".jpg";
    }

    public function has_avatar()
    {
        return (bool)$this->avatar_post_id;
    }

    public function avatar_path()
    {
        return RAILS_ROOT . "/public/data/avatars/" . $this->id . ".jpg";
    }

    public function set_avatar($params)
    {
        $post = Post::find($params['id']);
        if (!$post->can_be_seen_by($this)) {
            $this->errors()->add('access', "denied");
            return false;
        }
        
        if ($params['top'] < 0 or $params['top'] > 1 or
            $params['bottom'] < 0 or $params['bottom'] > 1 or
            $params['left'] < 0 or $params['left'] > 1 or
            $params['right'] < 0 or $params['right'] > 1 or
            $params['top'] >= $params['bottom'] or
            $params['left'] >= $params['right'])
        {
            $this->errors()->add('parameter', "error");
            return false;
        }

        $tempfile_path = RAILS_ROOT . "/public/data/" . $this->id . ".avatar.jpg";
        
        $use_sample = $post->has_sample();
        if ($use_sample) {
            $image_path = $post->sample_path();
            $image_ext = "jpg";
            $size = $this->_reduce_and_crop($post->sample_width, $post->sample_height, $params);

            # If we're cropping from a very small region in the sample, use the full
            # image instead, to get a higher quality image.
            if (($size['crop_bottom'] - $size['crop_top'] < CONFIG()->avatar_max_height) or
                ($size['crop_right'] - $size['crop_left'] < CONFIG()->avatar_max_width))
                $use_sample = false;
        }

        if (!$use_sample) {
            $image_path = $post->file_path();
            $image_ext = $post->file_ext;
            $size = $this->_reduce_and_crop($post->width, $post->height, $params);
        }
        
        try {
            Moebooru_Resizer::resize($image_ext, $image_path, $tempfile_path, $size, 95);
        } catch (Moebooru_Resizer_Error $x) {
            if (file_exists($tempfile_path))
                unlink($tempfile_path);

            $this->errors()->add("avatar", "couldn't be generated (" . $x->getMessage() . ")");
            return false;
        }
        
        rename($tempfile_path, $this->avatar_path());
        chmod($this->avatar_path(), 0775);
        
        $this->update_attributes(array(
            'avatar_post_id' => $params['post_id'],
            'avatar_top' => $params['top'],
            'avatar_bottom' => $params['bottom'],
            'avatar_left' => $params['left'],
            'avatar_right' => $params['right'],
            'avatar_width' => $size['width'],
            'avatar_height' => $size['height'],
            'avatar_timestamp' => gmd()
        ));
        
        return true;
    }
    
    private function _reduce_and_crop($image_width, $image_height, $params)
    {
        $cropped_image_width = $image_width * ($params['right'] - $params['left']);
        $cropped_image_height = $image_height * ($params['bottom'] - $params['top']);

        $size = Moebooru_Resizer::reduce_to(
            ['width' => $cropped_image_width, 'height' => $cropped_image_height],
            ['width' => CONFIG()->avatar_max_width, 'height' => CONFIG()->avatar_max_height],
            1, true);
        $size['crop_top'] = $image_height * $params['top'];
        $size['crop_bottom'] = $image_height * $params['bottom'];
        $size['crop_left'] = $image_width * $params['left'];
        $size['crop_right'] = $image_width * $params['right'];
        return $size;
    }
    # }


    # UserTagSubscriptionMethods {
    // protected function _on_tag_subscriptions_text_change($text)
    // {
      // User.transaction do
        // tag_subscriptions.clear

        // text.scan(/\S+/).each do |new_tag_subscription|
          // tag_subscriptions.create('tag_query' => new_tag_subscription)        }
      // end
    // }

    // def tag_subscriptions_text
      // tag_subscriptions_text.map(&:tag_query).sort.join(" ")
    // end

    // def tag_subscription_posts(limit, name)
      // TagSubscription.find_posts(id, name, limit)
    // end
    # }

    # UserLanguageMethods {
    protected function _on_secondary_language_array_change($langs)
    {
        $this->secondary_languages = $langs;
    }

    public function secondary_language_array()
    {
        if (!is_array($this->secondary_languages))
            $this->secondary_languages = explode(",", $this->secondary_languages);
        return $this->secondary_languages;
    }

    protected function _commit_secondary_languages()
    {
      if (!$this->secondary_languages)
        return;

      if (in_array("none", $this->secondary_languages))
        $this->secondary_languages = "";
      else
        $this->secondary_languages = implode(",", $this->secondary_languages);
    }
    # }

  // $this->salt = CONFIG()->password_salt

  // class << self
    // attr_accessor :salt
  // end

    # For compatibility with AnonymousUser class
    public function is_anonymous()
    {
        return !$this->level;
    }

    public function invited_by_name()
    {
        self::find_name($this->invited_by);
    }

    public function similar_users()
    {
        # This uses a naive cosine distance formula that is very expensive to calculate.
        # TODO: look into alternatives, like SVD.
        $sql = "
            SELECT
                f0.user_id as user_id,
                COUNT(*) / (SELECT sqrt((SELECT COUNT(*) FROM post_votes WHERE user_id = f0.user_id) * (SELECT COUNT(*) FROM post_votes WHERE user_id = {$this->id}))) AS similarity
            FROM
                vote v0,
                vote v1,
                users u
            WHERE
                v0.post_id = v1.post_id
                AND v1.user_id = {$this->id}
                AND v0.user_id <> {$this->id}
                AND u.id = v0.user_id
            GROUP BY v0.user_id
            ORDER BY similarity DESC
            LIMIT 6
        ";

        return self::select_all_sql($sql);
    }

    public function set_show_samples()
    {
        $this->show_samples = true;
    }

    static public function generate_sql($params)
    {
        $conds = $values = [];
        if (isset($params['name']) && (string)$params['name'] !== '') {
            $conds[] = "name LIKE ? ";
            $values[] = "%" . str_replace(" ", "_", $params['name']) . "%";
        }

        if (!empty($params['level']) && $params['level'] != "any") {
            $conds[] = "level = ?";
            $values[] = $params['level'];
        }
        
        if (!empty($params['id'])) {
            $conds[] = "id = ?";
            $values[] = $params['id'];
        }
        
        !$conds && $conds[] = 'true';
        
        !isset($params['order']) && $params['order'] = false;
        
        switch ($params['order']) {
            case "name":
                $order = "lower(name)";
                break;
            case "posts":
                $order = "(SELECT count(*) FROM posts WHERE user_id = users.id) DESC";
                break;
            case "favorites":
                $order = "(SELECT count(*) FROM favorites WHERE user_id = users.id) DESC";
                break;
            case "notes":
                $order = "(SELECT count(*) FROM note_versions WHERE user_id = users.id) DESC";
                break;
            default:
                $order = "id DESC";
                break;       
        }
        
        return ['conditions' => array_merge([implode(' AND ', $conds)], $values), 'order' => $order];
    }

    protected function _associations()
    {
        return array(
            'has_one' => array(
                'ban' => array('foreign_key' => 'user_id'),
                'user_blacklisted_tag'
            ),
            'belongs_to' => array(
                'avatar_post' => array('class_name' => "Post", 'foreign_key' => 'avatar_post_id')
            ),
            'has_many' => array(
                'post_votes',
                'user_logs',
                // 'user_blacklisted_tags' => array('dependent' => 'delete_all'),
                'tag_subscriptions' => array('order' => 'name', 'dependent' => 'delete_all')
            )
        );
    }
    
    protected function _callbacks()
    {
        $before_create = array('_set_role');
        if (CONFIG()->show_samples)
            $before_create[] = '_set_show_samples';
        
        return array(
            'before_create'     => $before_create,
            'before_save'       => array('_encrypt_password'),
            'before_validation' => array('_commit_secondary_languages'),
            'after_save'        => array('_commit_blacklists', '_update_cached_name'),
            'after_create'      => array('_set_default_blacklisted_tags', '_increment_count'),
            'after_destroy'     => array('_decrement_count')
        );
    }
    
    protected function _validations()
    {
        $vals = array(
            'name' => array(
                'length'     => ['2..20', 'on' => 'create'],
                'format'     => array('/\A[^\s;,]+\Z/', 'on' => 'create', 'message' => 'cannot have whitespace, commas, or semicolons'),
                'uniqueness' => array(true, 'on' => 'create')
            ),
            'password' => array(
                'length'       => array('5..', 'if' => array('property_exists' => 'password')),
                'confirmation' => true
            ),
            'language' => array(
                'format' => '/^([a-z\-]+)|$/'
            ),
            'secondary_languages' => array(
                'format' => '/^([a-z\-]+(,[a-z\0]+)*)?$/'
            ),
            array(
                # Changing password requires current password.
                '_validate_current_password'
            )
        );
        if (CONFIG()->enable_account_email_activation)
            $vals['email'] = array(
                'presence' => array(true, 'on' => 'create', 'if' => array('property_exists' => 'email'))
            );
        return $vals;
    }
    
    protected function _attr_protected()
    {
        return ['level', 'invite_count'];
    }
    
    private function _parse_is_level_or($method)
    {
        list($name, $operator) = explode('_or_', substr($method, 3));
        $name = ucfirst($name);
        $levels = CONFIG()->user_levels;
        
        if (!isset($levels[$name]))
            Rails::raise('InvalidArgumentException', "User level name not found for User::%s()", $method);
        
        $level = $levels[$name];
        if ($operator == 'higher') {
            return $this->level >= $level;
        } elseif ($operator == 'lower') {
            return $this->level <= $level;
        } else
            Rails::raise('InvalidArgumentException', "Invalid user level operator '%s'", $operator);
    }
    
    private function _parse_is_level($method)
    {
        $level_name = ucfirst(substr($method, 3));
        $levels = CONFIG()->user_levels;
        if (!isset($levels[$level_name]))
            Rails::raise('InvalidArgumentException', "User level name not found for User::%s()'", $method);
        
        return $this->level == $levels[$level_name];
    }
}