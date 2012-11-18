<?php
class Rails_ActionDispatch_Http_Parameters implements ArrayAccess, Iterator
{
    /**
     * Hold params that were set after
     * system initialization.
     */
    private $_params = array();
    
    /* ArrayAccess { */
    
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_params[] = $value;
        } else {
            $this->_params[$offset] = $value;
        }
    }
    
    public function offsetExists($offset)
    {
        switch (true) {
            case isset($this->_params[$offset]):
            case isset($_GET[$offset]):
            case isset($_POST[$offset]):
                return true;
        }
        return false;
    }
    
    public function offsetUnset($offset)
    {
        unset($this->_params[$offset]);
    }
    
    public function offsetGet($offset)
    {
        return isset($this->_params[$offset]) ? $this->_params[$offset] : null;
    }
    /* } Iterator {*/
    private $_position = 0;
    
    private $_current_var = 0;
    
    public function rewind()
    {
        reset($this->_params);
        reset($_GET);
        reset($_POST);
        $this->_current_var = 0;
        $this->_position = key($this->_params);
    }

    public function current()
    {
        switch ($this->_current_var) {
            case 0:
                return $this->_params[$this->_position];
            case 1:
                return $_GET[$this->_position];
            case 2:
                return $_POST[$this->_position];
        }
    }

    public function key()
    {
        switch ($this->_current_var) {
            case 0:
                return key($this->_params);
            case 1:
                return key($_GET);
            case 2:
                return key($_POST);
        }
    }

    public function next()
    {
        switch ($this->_current_var) {
            case 0:
                next($this->_params);
                $this->_position = key($this->_params);
                break;
            case 1:
                next($_GET);
                $this->_position = key($_GET);
                break;
            case 2:
                next($_POST);
                $this->_position = key($_POST);
                break;
        }
        
        if (!$this->_position) {
            if ($this->_current_var == 2)
                $this->_position = null;
            elseif (!$this->_current_var && empty($_GET) && empty($_POST))
                $this->_position = null;
            elseif (!$this->_current_var && empty($_GET))
                $this->_current_var = 2;
            elseif ($this->_current_var == 1 && empty($_POST))
                $this->_position = null;
            else {
                $this->_current_var++;
                $this->_current_var > 2 && $this->_current_var = 0;
                $var = $this->_current_array();
                $this->_position = key($var);
            }
        }
        
        switch ($this->_current_var) {
            case 0:
                $this->_position = key($this->_params);
                break;
            case 1:
                $this->_position = key($_GET);
                break;
            case 2:
                $this->_position = key($_POST);
                break;
        }
    }

    public function valid()
    {
        $var = $this->_current_array();
        return isset($var[$this->_position]);
    }
    
    private function _current_array()
    {
        switch ($this->_current_var) {
            case 0:
                return $this->_params;
            case 1:
                return $_GET;
            case 2:
                return $_POST;
        }
    }
    /* } */
    
    public function __get($prop)
    {
        switch (true) {
            case isset($this->_params[$prop]):
                return $this->_params[$prop];
            case isset($_GET[$prop]):
                return $_GET[$prop];
            case isset($_POST[$prop]):
                return $_POST[$prop];
            case $prop == 'controller':
                return Rails::application()->dispatcher()->router()->route()->controller;
            case $prop == 'action':
                return Rails::application()->dispatcher()->router()->route()->action;
            // case isset($_COOKIE[$prop]):
                // return $_COOKIE[$prop];
            // case ($prop == 'REQUEST_URI'):
                // return $this->getRequestUri();
            // case ($prop == 'PATH_INFO'):
                // return $this->getPathInfo();
            // case isset($_SERVER[$prop]):
                // return $_SERVER[$prop];
            // case isset($_ENV[$prop]):
                // return $_ENV[$prop];
            default:
                return null;
        }
    }
    
    public function all()
    {
        return array_merge($_POST, $_GET, $this->_params);
    }
    
    public function set($prop, $val)
    {
        $this->_params[$prop] = $val;
    }
    
    public function add($prop, $val)
    {
        $this->set($prop, $val);
    }
    
    public function get()
    {
        return $_GET;
    }
    
    public function post()
    {
        return $_POST;
    }
    
    /**
     * Returns params as array.
     */
    public function to_array()
    {
        parse_str($_SERVER['QUERY_STRING'], $arr);
        return $arr;
    }
}