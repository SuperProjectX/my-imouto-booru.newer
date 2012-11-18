<?php
class Note extends ActiveRecord_Base
{
    # TODO: move this to a helper
    public function formatted_body()
    {
        return $this->body = nl2br(preg_replace('/<tn>(.+?)<\/tn>/m', '<br /><p class="tn">\1</p>', $this->body));
    }
    
    protected function _associations()
    {
        return [
            'belongs_to' => ['post']
        ];
    }
}