<?php
abstract class ActionController
{
    /**
     * Helps with _run_filters, which is protected.
     */
    static public function run_filters_for(ApplicationController $ctrlr, $type)
    {
        $ctrlr->_run_filters($type);
    }
    
    static public function url_for($params)
    {
        Rails::load_klass('ActionController_UrlFor');
        $urlfor = new ActionController_UrlFor($params);
        return $urlfor->url();
    }
    
    public function params($name = null, $val = null)
    {
        if ($name)
            $this->_dispatcher()->parameters()->set($name, $val);
        return $this->_dispatcher()->parameters();
    }
    
    public function request()
    {
        return $this->_dispatcher()->request();
    }
    
    public function response()
    {
        return Rails::application()->dispatcher()->response();
    }
    
    public function session($index = null, $value = null)
    {
        if (!($num = func_num_args())) {
            
        } elseif ($num > 1) {
            $_SESSION[$index] = $value;
            return $this;
        } else {
            return isset($_SESSION[$index]) ? $_SESSION[$index] : null;
        }
    }
    
    /**
     * Retrieves Cookies instance, retrieves a cookie or
     * sets a cookie.
     */
    public function cookies($name = null, $val = null, array $params = array())
    {
        $num = func_num_args();
        
        if (!$num)
            return $this->_dispatcher()->response()->cookies();
        elseif ($num == 1)
            return $this->_dispatcher()->response()->cookies()->get($name);
        else
            return $this->_dispatcher()->response()->cookies()->add($name, $val, $params);
    }
    
    protected function _dispatcher()
    {
        return Rails::application()->dispatcher();
    }
}