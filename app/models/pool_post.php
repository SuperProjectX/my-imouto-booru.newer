<?php
class PoolPost extends ActiveRecord_Base
{
    const table_name = 'pools_posts';

    # iTODO:
    // versioned_parent :pool
    // versioning_group_by :class => :pool
    // versioned :active, :default => 'f', :allow_reverting_to_default => true
    // versioned :sequence
  
    protected function _associations()
    {
        return [
            'belongs_to' => [
                'post',
                'pool',
                'next_post' => ['class_name' => "Post", 'foreign_key' => "next_post_id"],
                'prev_post' => ['class_name' => "Post", 'foreign_key' => "prev_post_id"],
                
            ]
        ];
    }
    
    protected function _callbacks()
    {
        return [
            'after_save' => ['expire_cache']
        ];
    }
    
    public function can_change(User $user, $attribute)
    {
        if (!$user->is_member_or_higher())
            return false;
        return $pool->is_public || $user->has_permission($pool);
    }
    
    public function can_change_is_public($user)
    {
        return $user->has_permission($pool); # only the owner can change is_public
    }
    
    # This matches Pool.post_pretty_sequence in pool.js.
    public function pretty_sequence()
    {
        if (preg_match('/^[0-9]+.*/', $this->secuence))
            return "#".$this->sequence;
        else
            return '"'.$this->sequence.'"';
    }
    
    # Changing pool orderings affects pool sorting in the index.
    public function expire_cache()
    {
        // Rails::cache()->expire();
    }
    
    public function api_attributes()
    {
        $api_attributes = array('id', 'pool_id', 'post_id', 'active', 'sequence', 'next_post_id', 'prev_post_id');
        $api_attributes = array_fill_keys($api_attributes, null);
        
        foreach (array_keys($api_attributes) as $attr) {
            // # TODO: Don't know what 'active' is about. It's a "versioned".
            if (isset($this->$attr))
                $api_attributes[$attr] = $this->$attr;
        }
        
        return $api_attributes;
    }
    
    public function as_json()
    {
        return json_encode($this->api_attributes());
    }
}