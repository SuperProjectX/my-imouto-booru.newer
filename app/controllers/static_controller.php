<?php
class StaticController extends ApplicationController
{
    private $_test = 'yes';
    
    protected function _init()
    {
        $this->_layout('bare');
    }
    
    public function index()
    {
        $this->post_count = Post::fast_count();
    }
}