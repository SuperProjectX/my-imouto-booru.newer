<?php
class Rails_ActionDispatch_Http_Request
{
    // public function __call($prop, $params)
    // {
        // Rails::raise('Rails_ActionDispatch_Http_Request_Exception', "Call to unknown property '%s'.", $prop);
    // }
    
    public function path()
    {
        if (is_int($pos = strpos($_SERVER['REQUEST_URI'], '?')))
            return substr($_SERVER['REQUEST_URI'], 0, $pos);
        return substr($_SERVER['REQUEST_URI'], 0);
    }
    
    public function fullpath()
    {
        return $_SERVER['REQUEST_URI'];
    }
    
    public function controller()
    {
        return Rails::application()->dispatcher()->router()->route()->controller;
    }
    
    public function action()
    {
        return Rails::application()->dispatcher()->router()->route()->action;
    }
    
    public function get()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }
    
    public function post()
    {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    
    public function remote_ip()
    {
        if (!empty($_SERVER['HTTP_CLIENT_IP']))
            $remote_ip = $_SERVER['HTTP_CLIENT_IP'];
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            $remote_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else 
            $remote_ip = $_SERVER['REMOTE_ADDR'];
        return $remote_ip;
    }
    
    public function method()
    {
        return $_SERVER['REQUEST_METHOD'];
    }
    
    public function protocol()
    {
        $protocol = !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http';
        return $protocol . '://';
    }
    
    public function format()
    {
        return Rails::application()->dispatcher()->router()->route()->format;
    }
}