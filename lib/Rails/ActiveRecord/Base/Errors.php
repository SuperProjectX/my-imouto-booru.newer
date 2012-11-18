<?php
class ActiveRecord_Base_Errors
{
    const BASE_ERRORS_INDEX = 'model_base_errors';
    
    private $_errors = array();
    
    public function add($attribute, $msg = null)
    {
        if (!isset($this->_errors[$attribute]))
            $this->_errors[$attribute] = array();
        
        $this->_errors[$attribute][] = $msg;
    }
    
    public function add_to_base($msg)
    {
        $this->add(self::BASE_ERRORS_INDEX, $msg);
    }
    
    public function on($attribute)
    {
        if (!isset($this->_errors[$attribute]))
            return null;
        elseif (count($this->_errors[$attribute]) == 1)
            return current($this->_errors[$attribute]);
        else
            return $this->_errors[$attribute];
    }
    
    public function on_base()
    {
        return $this->on(self::BASE_ERRORS_INDEX);
    }
    
    # $glue is a string that, if present, will be used to
    # return the messages imploded.
    public function full_messages($glue = null)
    {
        $full_messages = array();
        
        foreach ($this->_errors as $attr => $errors) {
            foreach ($errors as $msg) {
                if ($attr == self::BASE_ERRORS_INDEX)
                    $full_messages[] = $msg;
                else
                    $full_messages[] = $this->_propper_attr($attr) . ' ' . $msg;
            }
        }
        
        if ($glue !== null)
            return implode($glue, $full_messages);
        else
            return $full_messages;
    }
    
    public function invalid($attribute)
    {
        return isset($this->_errors[$attribute]);
    }
    
    public function blank()
    {
        return !(bool)$this->_errors;
    }
    
    public function count()
    {
        $i = 0;
        foreach ($this->_errors as $errors) {
            $i += count($errors);
        }
        return $i;
    }
    
    private function _propper_attr($attr)
    {
        $attr = ucfirst(strtolower($attr));
        if (is_int(strpos($attr, '_'))) {
            $attr = str_replace('_', ' ', $attr);
        }
        return $attr;
    }
}