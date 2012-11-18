<?php
class Ban extends ActiveRecord_Base
{
    protected function _callbacks()
    {
        return [
            'before_create' => ['_save_level'],
            'after_create'  => ['_save_to_record', ':update_level'],
            'after_destroy' => ['_restore_level']
        ];
    }

    protected function _restore_level()
    {
        User::find($this->user_id)->update_attribute('level', $this->old_level);
    }
    
    protected function _save_level()
    {
        $this->old_level = User::find_level($this->user_id);
    }
    
    protected function _update_level()
    {
        $user = User::find($this->user_id);
        $user->level = CONFIG()->user_levels['Blocked'];
        $user->save();
    }
    
    # iTODO:
    protected function _save_to_record()
    {
        // UserRecord.create(:user_id => self.user_id, :reported_by => self.banned_by, :is_positive => false, :body => "Blocked: #{self.reason}")
    }
    
    protected function _on_duration_change($dur)
    {
        $seconds = $dur * 60*60*24;
        $this->expires_at = time('Y-m-d H:i:s', time() + $seconds);
        return $dur;
    }
}