<?php
class Dmail extends ActiveRecord_Base
{
    public function to_name()
    {
        if (!$this->to_id)
            return;
        return $this->to->name;
    }
    
    public function from_name()
    {
        return $this->from->name;
    }
    
    public function mark_as_read()
    {
        $this->update_attribute('has_seen', true);
        
        if (!Dmail::exists(["to_id = ? AND has_seen = false", current_user()->id]))
            current_user()->update_attribute('has_mail', false);
    }
    
    public function send_dmail()
    {
        if ($this->to->receive_dmails && is_int(strpos($this->to->email, '@')))
            UserMailer::deliver_dmail($this->to, $this->from, $this->title, $this->body);
    }
    
    public function update_recipient()
    {
        $this->to->update_attribute('has_mail', true);
    }
    
    protected function _validations()
    {
        return array(
            'to_id'   => array('presence' => true),
            'from_id' => array('presence' => true),
            'title'   => array('format' => '/\S/'),
            'body'    => array('format' => '/\S/')
        );
    }
    
    protected function _associations()
    {
        return array(
            'belongs_to' => array(
                'to'   => array('class_name' => 'User', 'foreign_key' => 'to_id'),
                'from' => array('class_name' => 'User', 'foreign_key' => 'from_id')
            )
        );
    }
    
    protected function _callbacks()
    {
        return [
            'after_create' => ['update_recipient', 'send_dmail']
        ];
    }
    
    protected function _init()
    {
        if ($this->parent_id)
            $this->title = "Re: " . $this->title;
    }
    
    protected function _on_to_name_change($name)
    {
        if (!$user = User::find_by_name($name))
            return;
        $this->to_id = $user->id;
    }
    
    protected function _on_from_name_change($name)
    {
        if (!$user = User::find_by_name($name))
            return;
        $this->from_id = $user->id;
    }
}