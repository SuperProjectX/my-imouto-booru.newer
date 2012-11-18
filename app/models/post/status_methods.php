<?php
trait PostStatusMethods
{
    protected function _on_status_change($s)
    {
        if ($s == $this->status)
                return;
        $this->status = $s;
        $this->touch_change_seq();
    }

    public function reset_index_timestamp() 
    {
        $this->index_timestamp = $this->created_at;
    }
    
    # Bump the post to the front of the index.
    public function touch_index_timestamp()
    {
        $this->index_timestamp = gmd();
    }

    public static function batch_activate($user_id, $post_ids)
    {
        $conds = $cond_params = array();
        
        if ($user_id) {
            $conds[] = "user_id = ?";
            $cond_params[] = $user_id;
        }
        
        $conds[] = "is_held";
        $conds[] = "id = ?";

        # Tricky: we want posts to show up in the index in the same order they were posted.
        # If we just bump the posts, the index_timestamps will all be the same, and they'll
        # show up in an undefined order.    We don't want to do this in the ORDER BY when
        # searching, because that's too expensive.    Instead, tweak the timestamps slightly:
        # for each post updated, set the index_timestamps 1ms newer than the previous.
        #
        # Returns the number of posts actually activated.
        $count = 0;
        
        # Original function is kinda confusing...
        # If anyone knows a better way to do this, it'll be welcome.
        sort($post_ids);
        
        $s = 1;
        $timestamp = new DateTime(self::find_index_timestamp(array('order' => 'id DESC')));
        
        foreach ($post_ids as $id) {
            $timestamp->add(new DateInterval('PT' . $s . 'S'));
            
            $params = array_merge(array('posts SET index_timestamp = ?, is_held = 0 WHERE ' . implode(' AND ', $conds)), array($timestamp->format('Y-m-d H:i:s')), $cond_params, array($id));
            
            if (call_user_func_array('DB::update', $params)) {
                $count++;
                $s++;
            }
        }

        // Cache.expire if count > 0

        return $count;
    }

    public function update_status_on_destroy()
    {
        # Can't use update_attributes here since this method is wrapped inside of a destroy call
        self::execute_sql("UPDATE posts SET status = ? WHERE id = ?", "deleted", $this->id);
        if ($this->parent_id)
                Post::update_has_children($this->parent_id);
        if ($this->flag_detail)
                $this->flag_detail->update_attribute('is_resolved', true);
        return false;
    }

    public function commit_status_reason()
    {
        if (!$this->status_reason)
                return;
        $this->set_flag_detail($this->status_reason, null);
    }

    protected function _on_is_held_change($hold)
    {
        # Hack because the data comes in as a string:
        if ($hold == "false")
            $hold = false;

        $user = current_user();

        # Only the original poster can hold or unhold a post.
        if ($user && !$user->has_permission($this))
            return;

        if ($hold) {
            # A post can only be held within one minute of posting (except by a moderator);
            # this is intended to be used on initial posting, before it shows up in the index.
            if ($this->created_at && strtotime($this->created_at) < strtotime('-1 minute'))
                return;
        }

        $was_held = $this->is_held;

        $this->is_held = $hold;

        # When a post is unheld, bump it.
        if ($was_held && !$hold) {
            $this->touch_index_timestamp();
        }
    }

    public function undelete()
    {
        if ($this->status == "active") return;
        $this->update_attribute('status', "active");
        $this->_run_callbacks('after_undelete');
    }
}