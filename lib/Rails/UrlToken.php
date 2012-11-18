<?php
class Rails_UrlToken
{
    const SEPARATOR = '#';
    
    private $_controller;
    
    private $_action = 'index';
    
    public function __construct($token)
    {
        is_array($token) && vde($token);
        if (is_bool(strpos($token, self::SEPARATOR)))
            Rails::raise('Rails_UrlToken_Exception', "Missing separator in token '%s'", $token);
        $parts = explode(self::SEPARATOR, $token);
        
        if (empty($parts[0])) {
            $parts[0] = Rails::application()->dispatcher()->router()->route()->controller;
            // Rails::raise('Rails_UrlToken_Exception', "Missing controller in token '%s'", $token);
        }
        $this->_controller = $parts[0];
        
        if (!empty($parts[1]))
            $this->_action = $parts[1];
    }
    
    public function parts()
    {
        return array($this->_controller, $this->_action);
    }
    
    public function controller()
    {
        return $this->_controller;
    }
    
    public function action()
    {
        return $this->_action;
    }
    
    public function token()
    {
        return $this->_controller . self::SEPARATOR . $this->_action;
    }
}