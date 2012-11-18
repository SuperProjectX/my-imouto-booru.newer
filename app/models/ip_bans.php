<?php
class IpBans extends ActiveRecord_Base
{
    const table_name = 'ip_bans';
    
    protected function _associations()
    {
        return array(
            'belongs_to' => array(
                'user' => array('foreign_key' => 'banned_by')
            )
        );
    }

    protected function _on_duration_change($dur)
    {
        if (!$dur) {
            $this->expires_at = '00-00-00 00:00:00';
            $duration = null;
        } else {
            $this->expires_at = date('Y-m-d H:i:s', strtotime('-1 day'));
            $duration = $dur;
        }
        
        return $duration;
    }
}