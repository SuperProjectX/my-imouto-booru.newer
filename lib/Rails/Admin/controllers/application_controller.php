<?php
class ApplicationController extends ActionController_Base
{
    final protected function _before_filter()
    {
        return array('_is_user_allowed');
    }
    
    final protected function _is_user_allowed()
    {
        if (!in_array($this->request()->remote_ip(), Rails::application()->config('app', 'rails_admin_ips'))) {
            $this->_render(array('action' => 'forbidden'), array('status' => 403));
        }
    }
}