<?php
ActionView::add_helpers(array('post', 'tag_subscription', 'avatar'));

class UserController extends ApplicationController
{
    protected function _filters()
    {
        return [
            'before' => [
                ['blocked_only', 'only' => ['authenticate', 'update', 'edit', 'modify_blacklist']],
                ['janitor_only', 'only' => ['invites']],
                ['mod_only', 'only' => ['block', 'unblock', 'show_blocked_users']],
                ['post_member_only', 'only' => ['set_avatar']],
                ['no_anonymous', 'only' => ['change_password', 'change_email']]
            ]
        ];
    }
    
    // public function autocomplete_name()
    // {
        // keyword = $this->params()->term.to_s
        // $this->users = User.where(['name ILIKE ?', "*#{keyword}*".to_escaped_for_sql_like]).pluck(:name) if keyword.length >= 2
        // $this->respond_to(array(
            // format.json { $this->render(array('json' => ($this->users || array()) })
        // ));
    // }

    # FIXME: this method is crap and only function as temporary workaround
    #                until I convert the controllers to resourceful version which is
    #                planned for 3.2 branch (at least 3.2.1).
    public function remove_avatar()
    {
        # When removing other user's avatar, ensure current user is mod or higher.
         if (current_user()->id != $this->params()->id and !current_user()->is_mod_or_higher()) {
            $this->_access_denied();
            return;
        }
        $this->user = User::find($this->params()->id);
        $this->user->avatar_post_id = null;
        if ($this->user->save()) {
            $this->_notice('Avatar removed');
        } else {
            $this->_notice('Failed removing avatar');
        }
        $this->_redirect_to(['#show', 'id' => $this->params()->id]);
    }

    public function change_password()
    {
        $this->title = 'Change Password';
    }

    public function change_email()
    {
        $this->title = 'Change Email';
        current_user()->current_email = current_user()->email;
        $this->user = current_user();
    }

    public function show()
    {
        if ($this->params()->name) {
            $this->user = User::find_by_name($this->params()->name);
        } else {
            $this->user = User::find($this->params()->id);
        }

        if (!$this->user) {
            $this->_redirect_to("/404");
        } else {
            if ($this->user->id == current_user()->id)
                $this->_set_title('My profile');
            else
                $this->_set_title($this->user->name . "'s profile");
        }
        
        if (current_user()->is_mod_or_higher()) {
            // $this->user_ips = $this->user->user_logs->order('created_at DESC').pluck('ip_addr').uniq
            $this->user_ips = array();
        }
        
        $tag_types = CONFIG()->tag_types;
        foreach (array_keys($tag_types) as $k) {
            if (!preg_match('/^[A-Z]/', $k) || $k == 'General' || $k == 'Faults')
                unset($tag_types[$k]);
        }
        $this->tag_types = $tag_types;
        
        ActiveRecord::load_model(array('History', 'NoteVersion', 'WikiPageVersion'));
        
        $this->_respond_to(array(
            'html'
        ));
    }

    // public function invites()
    // {
        // if ($this->request()->post()) {
             // if ($this->params()->member) {
                // begin
                    // current_user()->invite!($this->params()->member[:name], $this->params()->member[:level])
                    // flash[:notice] = "User was invited"

                // rescue ActiveRecord::RecordNotFound
                    // flash[:notice] = "Account not found"

                // rescue User::NoInvites
                    // flash[:notice] = "You have no invites for use"

                // rescue User::HasNegativeRecord
                    // flash[:notice] = "This use has a negative record and must be invited by an admin"
                // end
            // }

            // redirect_to '#invites'
        // } else {
            // $this->invited_users = User.find(:all, 'conditions' => ["invited_by = ?", current_user()->id], 'order' => "lower(name)")
        // }
    // }

    public function home()
    {
        $this->_set_title('My Account');
    }

    public function index()
    {
        $this->_set_title('Users');
        
        ActiveRecord::load_model('NoteVersion');
        $this->users = User::paginate(User::generate_sql(array_merge($this->params()->all(), ['per_page' => 20, 'page' => $this->page_number()])));
        $this->_respond_to_list("users");
    }

    public function authenticate()
    {
        $this->_save_cookies(current_user());
        $path = $this->params()->url ?: '#home';
        $this->_respond_to_success("You are now logged in", $path);
    }

    public function check()
    {
        if (!$this->request()->post()) {
            $this->_redirect_to('root');
            return;
        }
        
        $user = User::find_by_name($this->params()->username);
        
        $ret['exists'] = false;
        $ret['name'] = $this->params()->username;

        if (!$user) {
            $ret['response'] = "unknown-user";
            $this->_respond_to_success("User does not exist", array(), array('api' => $ret));
            return;
        }

        # Return some basic information about the user even if the password isn't given, for
        # UI cosmetics.
        $ret['exists']   = true;
        $ret['id']       = $user->id;
        $ret['name']     = $user->name;
        $ret['no_email'] = !((bool)$user->email);

        $pass = $this->params()->password ?: "";

        $user = User::authenticate($this->params()->username, $pass);

        if (!$user) {
            $ret['response'] = "wrong-password";
            $this->_respond_to_success("Wrong password", array(), array('api' => $ret));
            return;
        }

        $ret['pass_hash'] = $user->password_hash;
        $ret['user_info'] = $user->user_info_cookie();
        $ret['response']  = 'success';
        
        $this->_respond_to_success("Successful", array(), array('api' => $ret));
    }

    public function login()
    {
        $this->_set_title('Login');
    }

    public function create()
    {
        $user = User::create($this->params()->user);

         if ($user->errors()->blank()) {
            $this->_save_cookies($user);

            $ret = [
                'exists'    => false,
                'name'      => $user->name,
                'id'        => $user->id,
                'pass_hash' => $user->password_hash,
                'user_info' => $user->user_info_cookie()
            ];

            $this->_respond_to_success("New account created", '#home', ['api' => array_merge(['response' => "success"], $ret)]);
        } else {
            $error = $user->errors()->full_messages(", ");
            $this->_respond_to_success("Error: " . $error, '#signup', ['api' => ['response' => "error", 'errors' => $user->errors()->full_messages()]]);
        }
    }

    public function signup()
    {
        $this->_set_title('Signup');
        $this->user = new User();
    }

    public function logout()
    {
        $this->_set_title('Logout');
        $this->session('user_id', null);
        $this->cookies('login', null);
        $this->cookies('pass_hash', null);

        $dest = $this->params()->from ?: '#home';
        $this->_respond_to_success("You are now logged out", $dest);
    }

    public function update()
    {
         if ($this->params()->commit == "Cancel") {
            $this->_redirect_to('#home');
            return;
        }

        if (current_user()->update_attributes($this->params()->user)) {
            $this->_respond_to_success("Account settings saved", '#edit');
        } else {
            if ($this->params()->render and $this->params()->render['view']) {
                $this->_render(['action' => $this->_get_view_name_for_edit($this->params()->render['view'])]);
            } else {
                $this->_respond_to_error(current_user(), '#edit');
            }
        }
    }
    
    public function modify_blacklist()
    {
        $added_tags = $this->params()->add ?: [];
        $removed_tags = $this->params()->remove ?: [];

        $tags = current_user()->blacklisted_tags_array();
        foreach ($added_tags as $tag) {
            if (!in_array($tag, $tags))
                $tags[] = $tag;
        }
        
        $tags = array_diff($tags, $removed_tags);
        
        if (current_user()->user_blacklisted_tag->update_attribute('tags', implode("\n", $tags))) {
            $this->_respond_to_success("Tag blacklist updated", '#home', ['api' => ['result' => current_user()->blacklisted_tags_array()]]);
        } else {
            $this->_respond_to_error(current_user(), '#edit');
        }
    }

    public function remove_from_blacklist()
    {
    }

    public function edit()
    {
        $this->_set_title('Edit Account');
        $this->user = current_user();
    }

    public function reset_password()
    {
        $this->_set_title('Reset Password');
        
        if ($this->request()->post()) {
            $this->user = User::find_by_name($this->params()->user['name']);

            if (!$this->user) {
                $this->_respond_to_error("That account does not exist", '#reset_password', ['api' => ['result' => "unknown-user"]]);
                return;
            }

            if (!$this->user->email) {
                $this->_respond_to_error("You never supplied an email address, therefore you cannot have your password automatically reset",
                                                 '#login', ['api' => ['result' => "no-email"]]);
                return;
            }

            if ($this->user->email != $this->params()->user['email']) {
                $this->_respond_to_error("That is not the email address you supplied",
                                                 '#login', ['api' => ['result' => "wrong-email"]]);
                return;
            }
            
            # iTODO:
            try {
                // User.transaction do
                    # If the email is invalid, abort the password reset
                    $new_password = $this->user->reset_password();
                    UserMailer::new_password($this->user, $new_password)->deliver();
                    $this->_respond_to_success("Password reset. Check your email in a few minutes.",
                                                     '#login', ['api' => ['result' => "success"]]);
                    return;
                // end
            } catch (Exception $e) { // rescue Net::SMTPSyntaxError, Net::SMTPFatalError
                $this->_respond_to_success("Your email address was invalid",
                                                 '#login', ['api' => ['result' => "invalid-email"]]);
                return;
            }
        } else {
            $this->user = new User();
            if ($this->params()->format and $this->params()->format != 'html')
                $this->_redirect_to('root');
        }
    }

    public function block()
    {
        $this->user = User::find($this->params()->id);

        if ($this->request()->post()) {
            if ($this->user->is_mod_or_higher()) {
                $this->_notice("You can not ban other moderators or administrators");
                $this->_redirect_to('#block');
                return;
            }
            !is_array($this->params()->ban) && $this->params()->ban = [];
            
            $attrs = array_merge($this->params()->ban, ['banned_by' => current_user()->id, 'user_id' => $this->params()->id]);
            Ban::create($attrs);
            $this->_redirect_to('#show_blocked_users');
        } else {
            $this->ban = new Ban(['user_id' => $this->user->id, 'duration' => "1"]);
        }
    }

    public function unblock()
    {
        foreach (array_keys($this->params()->user) as $user_id)
            Ban::destroy_all(["user_id = ?", $user_id]);

        $this->_redirect_to('#show_blocked_users');
    }

    public function show_blocked_users()
    {
        $this->_set_title('Blocked Users');
        
        ActiveRecord::load_model('IpBans');
        
        #$this->users = User.find(:all, 'select' => "users.*", 'joins' => "JOIN bans ON bans.user_id = users.id", 'conditions' => ["bans.banned_by = ?", current_user()->id])
        $this->users = User::find_all(['select' => "users.*", 'joins' => "JOIN bans ON bans.user_id = users.id", 'order' => "expires_at ASC"]);
        $this->ip_bans = IpBans::find_all();
    }

    /**
     * Moebooru doesn't use email activation,
     * so these methods aren't used.
     * Also, User::confirmation_hash() method is missing.
     */
    // public function resend_confirmation()
    // {
        // if (!CONFIG()->enable_account_email_activation) {
            // $this->_access_denied();
            // return;
        // }
        
        // if ($this->request()->post()) {
            // $user = User::find_by_email($this->params()->email);

            // if (!$user) {
                // $this->_notice("No account exists with that email");
                // $this->_redirect_to('#home')
                // return;
            // }

            // if ($user->is_blocked_or_higher()) {
                // $this->_notice("Your account is already activated");
                // $this->_redirect_to('#home');
                // return;
            // }

            // UserMailer::deliver_confirmation_email($user);
            // $this->_notice("Confirmation email sent");
            // $this->_redirect_to('#home');
        // }
    // }

    // public function activate_user()
    // {
        // if (!CONFIG()->enable_account_email_activation) {
            // $this->_access_denied();
            // return;
        // }
        
        // $this->_notice("Invalid confirmation code");

        // $users = User::find_all(['conditions' => ["level = ?", CONFIG()->user_levels["Unactivated"]]]);
        // foreach ($users as $user) {
            // if (User::confirmation_hash($user->name) == $this->params()->hash) {
                // $user->update_attribute('level', CONFIG()->starting_level);
                // $this->_notice("Account has been activated");
                // break;
            // }
        // }

        // $this->_redirect_to('#home');
    // }

    public function set_avatar()
    {
        $this->user = current_user();
        if ($this->params()->user_id) {
            $this->user = User::find($this->params()->user_id);
            if (!$this->user);
                $this->_respond_to_error("Not found", '#index', ['status' => 404]);
        }

        if (!$this->user->is_anonymous() && !current_user()->has_permission($this->user, 'id')) {
            $this->_access_denied();
            return;
        }

        if ($this->request()->post()) {
            if ($this->user->set_avatar($this->params()->all())) {
                $this->_redirect_to(['#show', 'id' => $this->user->id]);
            } else {
                $this->_respond_to_error($this->user, '#home');
            }
        }

         if (!$this->user->is_anonymous() && $this->params()->id == $this->user->avatar_post_id) {
            $this->old = $this->params();
        }

        $this->params = $this->params();
        $this->post = Post::find($this->params()->id);
    }

    public function error()
    {
        $report = $this->params()->report;

        $file = RAILS_ROOT . "/log/user_errors.log";
        if (!is_file($file)) {
            $fh = fopen($file, 'a');
            fclose($fh);
        }
        file_put_contents($file, $report . "\n\n\n-------------------------------------------\n\n\n", FILE_APPEND);

        $this->_render(array('json' => array('success' => true)));
    }
    
    protected function _save_cookies($user)
    {
        $this->cookies('login', $user->name, array('expires' => strtotime('+1 year')));
        $this->cookies('pass_hash', $user->password_hash, array('expires' => strtotime('+1 year')));
        $this->cookies('user_id', $user->id, array('expires' => strtotime('+1 year')));
        $this->session('user_id', $user->id);
    }
    
    protected function _get_view_name_for_edit($param)
    {
        switch ($param) {
            case 'change_email':
                return 'change_email';
            case 'change_password':
                return 'change_password';
            default:
                return 'edit';
        }
    }
}