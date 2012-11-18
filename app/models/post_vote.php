<?php
class PostVote extends ActiveRecord_Base
{
    protected function _associations()
    {
        return array(
            'belongs_to' => array(
                  'post',
                  'user'
            )
        );
    }
}