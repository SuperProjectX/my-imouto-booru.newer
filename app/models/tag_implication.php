<?php
class TagImplication extends ActiveRecord_Base
{
    static public function with_implied($tags)
    {
        if (!$tags)
            return array();
        
        $all = array();

        foreach ($tags as $tag) {
            $all[] = $tag;
            $results = array($tag);

            foreach(range(1, 10) as $i) {
                $results = self::select_row('
                SELECT
                    t1.name 
                    FROM tags t1, tags t2, tag_implications ti 
                    WHERE ti.predicate_id = t2.id 
                    AND ti.consequent_id = t1.id 
                    AND t2.name IN (?)
                    AND ti.is_pending = FALSE
                ', $results);
                
                if (is_array($results)) {
                    $results = array_values($results);
                    $all = array_merge($all, $results);
                } else
                    break;
            }
        }
        
        return $all;
    }
    
    public function destroy_and_notify($current_user, $reason)
    {
        # TODO:
        if (!empty($this->creator_id) && $this->creator_id != $current_user->id) {
            include_model('Dmail');
            $msg = "A tag implication you submitted (".$this->predicate->name." &rarr; ".$this->consequent->name.") was deleted for the following reason: ".$reason;
            Dmail::create(array('from_id' => current_user()->id, 'to_id' => $this->creator_id, 'title' => "One of your tag implications was deleted", 'body' => $msg));
        }
        
        $this->destroy();
    }
    
    public function approve($user_id, $ip_addr)
    {
        self::execute_sql("UPDATE tag_implications SET is_pending = FALSE WHERE id = " . $this->id);
        
        $t = Tag::find($this->predicate_id);
        $implied_tags = implode(' ', self::with_implied(array($t->name)));
        
        foreach (Post::find_all(array('conditions' => array("id IN (SELECT pt.post_id FROM posts_tags pt WHERE pt.tag_id = ?)", $t->id))) as $post) {
            $post->update_attributes(array('tags' => $post->tags . " " . $implied_tags, 'updater_user_id' => $user_id, 'updater_ip_addr' => $ip_addr));
        }
    }
    
    protected function _associations()
    {
        return array(
            'belongs_to' => array(
                'predicate' => array('class_name' => 'Tag', 'foreign_key' => 'predicate_id'),
                'consequent' => array('class_name' => 'Tag', 'foreign_key' => 'consequent_id')
            )
        );
    }
    
    protected function _callbacks()
    {
        return array(
            'before_create' => array('_validate_uniqueness')
        );
    }
    
    protected function _validate_uniqueness()
    {
        $this->_predicate_is($this->new_predicate);
        $this->_consequent_is($this->new_consequent);
        
        if (self::find_first(array('conditions' => array("(predicate_id = ? AND consequent_id = ?) OR (predicate_id = ? AND consequent_id = ?)", $this->predicate_id, $this->consequent_id, $this->consequent_id, $this->predicate_id)))) {
            $this->errors()->add_to_base("Tag implication already exists");
            return false;
        }
    }
    
    private function _predicate_is($name)
    {
        $t = Tag::find_or_create_by_name($name);
        $this->predicate_id = $t->id;
    }

    private function _consequent_is($name)
    {
        $t = Tag::find_or_create_by_name($name);
        $this->consequent_id = $t->id;
    }
    
}