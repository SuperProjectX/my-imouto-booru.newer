<?php
class ActionController_Cookie
{
    protected $_name;
    
    protected $_value;
    
    protected $_expire;
    
    protected $_path;
    
    protected $_domain;
    
    protected $_secure = false;
    
    protected $_httponly = false;
    
    public function __construct($name, $value = '', $expire = null, $path = null, $domain = null, $secure = false, $httponly = false)
    {
        $this->_set_default_values();
        
        $this->_name     = $name;
        $this->_value    = $value;
        $this->_expire   = $expire;
        $this->_path     = $path;
        $this->_domain   = $domain;
        $this->_secure   = $secure;
        $this->_httponly = $httponly;
    }
    
    public function set()
    {
        return setcookie($this->_name, $this->_value, $this->_expire, $this->_path, $this->_domain, $this->_secure, $this->_httponly);
    }
    
    private function _set_default_values()
    {
        $config = Rails::application()->config('cookies');
        foreach ($config as $k => $val) {
            $prop = '_' . $k;
            # Silently ignore unknown properties.
            if (property_exists($this, $prop))
                $this->$prop = $val;
        }
    }
}