<?php
class ForumPost extends ActiveRecord_Base
{
    protected function _associations()
    {
        return [
            'belongs_to' => [
                'creator' => ['class_name' => 'User', 'foreign_key' => 'creator_id'],
                'updater' => ['class_name' => 'User', 'foreign_key' => 'last_updated_by'],
                'parent'  => ['class_name' => "ForumPost", 'foreign_key' => 'parent_id']
            ],
            'has_many' => [
                'children' => ['class_name' => "ForumPost", 'foreign_key' => 'parent_id', 'order' => "id"]
            ]
        ];
    }
    
    protected function _callbacks()
    {
        return [
            'after_create'      => ['initialize_last_updated_by', 'update_parent_on_create'],
            'before_destroy'    => ['update_parent_on_destroy'],
            'before_validation' => ['validate_title', 'validate_lock']
        ];
    }
    
    protected function _validations()
    {
        return [
            'body' => [
                'length' => ['1..', 'message' => "You need to enter a body"]
            ]
        ];
    }

    /* LockMethods { */
    
    static public function lock($id)
    {
        # Run raw SQL to skip the lock check
        self::execute_sql("UPDATE forum_posts SET is_locked = TRUE WHERE id = ?", $id);
    }

    static public function unlock($id)
    {
        # Run raw SQL to skip the lock check
        self::execute_sql("UPDATE forum_posts SET is_locked = FALSE WHERE id = ?", $id);
    }

    public function validate_lock()
    {
        if ($this->root()->is_locked) {
            $this->errors()->add('base', "Thread is locked");
            return false;
        }
        return true;
    }
    
    /* } StickyMethods { */
    
    static public function stick($id)
    {
        # Run raw SQL to skip the lock check
        self::execute_sql("UPDATE forum_posts SET is_sticky = TRUE WHERE id = ?", $id);
    }

    static public function unstick($id)
    {
        # Run raw SQL to skip the lock check
        self::execute_sql("UPDATE forum_posts SET is_sticky = FALSE WHERE id = ?", $id);
    }
    
    /* } ParentMethods { */

    public function update_parent_on_destroy()
    {
        if (!$this->is_parent()) {
            $p = $this->parent;
            $p->update_attributes(['response_count' => $p->response_count - 1]);
        }
    }

    public function update_parent_on_create()
    {
        if (!$this->is_parent()) {
            $p = $this->parent;
            $p->update_attributes(['updated_at' => $this->updated_at, 'response_count' => $p->response_count + 1, 'last_updated_by' => $this->creator_id]);
        }
    }

    public function is_parent()
    {
        return !(bool)$this->parent_id;
    }

    public function root()
    {
        if ($this->is_parent()) {
            return $this;
        } else {
            return $this->parent;
        }
    }

    public function root_id()
    {
        if ($this->is_parent()) {
            return $this->id;
        } else {
            return $this->parent_id;
        }
    }
    
    /* } ApiMethods { */
    
    public function api_attributes()
    {
        return [
            'body'       => $this->body,
            'creator'    => $this->author(),
            'creator_id' => $this->creator_id,
            'id'         => $this->id,
            'parent_id'  => $this->parent_id,
            'title'      => $this->title
        ];
    }

    public function as_json(array $params = [])
    {
        return $this->api_attributes();
    }

    public function to_xml(array $options = [])
    {
        return parent::to_xml($this->api_attributes, ['root' => "forum_post"]);
    }
    
    /* } */
    
    static public function updated($user)
    {
        $conds = [];
        if (!$user->is_anonymous())
            $conds = ["creator_id <> " . $user->id];

        $newest_topic = ForumPost::find_first(['order' => "id desc", 'limit' => 1, 'select' => "created_at", 'conditions' => $conds]);
        
        if (!$newest_topic)
            return false;
        return $newest_topic->created_at > $user->last_forum_topic_read_at;
    }

    public function validate_title()
    {
        if ($this->is_parent()) {
            if (!$this->title || !preg_match('/\S/', $this->title)) {
                $this->errors()->add('title', "missing");
                return false;
            }
        }
        return true;
    }

    public function initialize_last_updated_by()
    {
        if ($this->is_parent()) {
            $this->update_attribute('last_updated_by', $this->creator_id);
        }
    }

    public function last_updater()
    {
        return $this->updater ? $this->updater->name : CONFIG()->default_guest_name;
    }

    public function author()
    {
        return $this->creator->name;
    }
}