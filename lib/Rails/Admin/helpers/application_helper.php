<?php
class ApplicationHelper extends ActionView_Helper
{
    public function link_to($link, $url_params, array $attrs = array())
    {
        if ($url_params == 'root') {
            return parent::link_to($link, $url_params, $attrs);
        } else {
            $attrs['href'] = '/' . Rails::application()->config('app', 'rails_admin_url') . '/' . substr($url_params, 1);
            return $this->content_tag('a', $link, $attrs);
        }
    }
}