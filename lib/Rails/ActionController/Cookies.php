<?php
/**
 * This class will be available in controllers by
 * calling '$this->cookies()'.
 *
 * Cookies can be set through this class. The cookies won't
 * be actually sent to the browser until the controller
 * finishes its work.
 *
 * Doing '$cookies->some_cookie' will call __get,
 * which checks the $_COOKIE variable. In other words, it will
 * check if the request sent a cookie named 'some_cookie'.
 * If not, it will return null.
 *
 * To set a cookie, use add(), to remove them use remove().
 * To check if a cookie was set by the controller, use in_jar().
 */
class ActionController_Cookies
{
    /**
     * Holds cookies that will be added at the
     * end of the controller.
     */
    private $_jar = array();
    
    /**
     * To know if cookies were set or not.
     */
    private $_cookies_set = false;
    
    /**
     * @see get()
     */
    public function __get($prop)
    {
        return $this->get($prop);
    }
    
    public function add($name, $value = '', array $params = array())
    {
        $p = array_merge(array(
            'expires' => null,
            'path'    => '/',
            'domain'  => null,
            'secure'  => false,
            'httponly'=> false
        ), $params);
        
        Rails::load_klass('ActionController_Cookie');
        
        $this->_jar[$name] = new ActionController_Cookie($name, $value, $p['expires'], $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    
    public function remove($name)
    {
        unset($this->_jar[$name]);
    }
    
    public function delete($name, array $params = array())
    {
        $params['expires'] = time() - 31536000;
        $this->add($name, '', $params);
    }
    
    /**
     * Checks if a cookie was created and is in the jar.
     */
    public function in_jar($name)
    {
        return isset($this->_jar[$name]);
    }
    
    /**
     * Retrieves cookies from _jar or _COOKIE.
     * Keep in mind the _jar > _COOKIE order.
     */
    public function get($name)
    {
        if (isset($this->_jar[$name]))
            return $this->_jar[$name];
        elseif (isset($_COOKIE[$name]))
            return $_COOKIE[$name];
        else
            return null; 
    }
    
    /**
     * Actually sets cookie in headers.
     * They can only be set once.
     */
    public function set()
    {
        if (!$this->_cookies_set) {
            foreach ($this->_jar as $c)
                $c->set();
            $this->_cookies_set = true;
        }
    }
}