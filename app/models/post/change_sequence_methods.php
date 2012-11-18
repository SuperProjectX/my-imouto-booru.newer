<?php
trait PostChangeSequenceMethods
{
    // attr_accessor :increment_change_seq
    
    public function touch_change_seq()
    {
        $this->increment_change_seq = true;
    }

    public function update_change_seq()
    {
        if (!$this->increment_change_seq)
            return;
        self::execute_sql("UPDATE posts SET change_seq = nextval('post_change_seq') WHERE id = ?", $this->id);
        $this->change_seq = self::select_value("SELECT change_seq FROM posts WHERE id = ?", $this->id);
    }
}