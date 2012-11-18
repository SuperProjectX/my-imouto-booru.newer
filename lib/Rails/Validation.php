<?php
class Rails_Validation
{
    protected
        $_success,
    
        $_result,
    
        $_type,
    
        $_data,
    
        $_rule,
    
        $_params;
    
    public function __construct($type, $data, $rule, array $params = array())
    {
        $this->_type   = $type;
        $this->_data   = $data;
        $this->_rule   = $rule;
        $this->_params = $params;
    }
    
    public function validate()
    {
        $validate_method = '_validate_' . $this->_type;
        if (!method_exists($this, $validate_method)) {
            Rails::raise('Rails_Validation_Exception', "Validation for '%s' isn't supported", $this->_type);
        }
        
        $this->_success = $this->$validate_method();
        // if ($validate_method == '_validate_uniqueness')
            // vde($this->_success);
        return $this;
    }
    
    public function success()
    {
        return $this->_success;
    }
    
    protected function _validate_length()
    {
        $this->_data = strlen($this->_data);
        $this->_params['only_integer'] = true;
        return $this->_validate_number();
    }
    
    protected function _validate_format()
    {
        return (bool)preg_match($this->_rule, $this->_data);
    }
    
    protected function _validate_number()
    {
        $result = 2;
        $this->_convert_number($this->_data);
        
        if (is_string($this->_rule) && is_int(strpos($this->_rule, '..'))) {
            list($min, $max) = explode('..', $this->_rule);
            
            $this->_convert_number($min);
            
            if ($this->_data < $min)
                $result = -1;
            elseif ($max !== '') {
                $this->_convert_number($max);
                if ($this->_data > $max)
                    $result = 1;
            }
        } else {
            if (!is_bool($this->_rule)) {
                # Comparing to a number.
                $this->_convert_number($this->_rule);
                if ($this->_data < $this->_rule)
                    $result = -1;
                elseif ($this->_data > $this->_rule)
                    $result = 1;
            }
        }
        
        # Check params. {
        if (!empty($this->_params['even'])) {
            if (($this->_data % 2))
                $success = false;
        } elseif (!empty($this->_params['odd'])) {
            if (($this->_data % 1))
                $success = false;
        }
        # }
        
        if (isset($success) && $success === false) {
            $this->_result = $result;
        } elseif ($result !== 2) {
            $success = false;
            $this->_result = $result;
        } else
            $success = true;
        
        return $success;
    }
    
    protected function _validate_inclusion()
    {
        if (is_array($this->_rule)) {
            if (!in_array($this->_data, $this->_rule))
                return false;
        } elseif (is_string($this->_rule) && is_int(strpos($this->_rule, '..'))) {
            return $this->_validate_number();
        } else {
            Rails::raise('Rails_Validation_Exception',
                         "Invalid inclusion validation rule, must be either an array or a numeric rule");
        }
    }
    
    protected function _validate_exclusion()
    {
        if (is_array($this->_rule)) {
            if (in_array($this->_data, $this->_rule))
                return false;
        } elseif (is_string($this->_rule) && is_int(strpos($this->_rule, '..'))) {
            return !($this->_validate_number());
        } else {
            Rails::raise('Rails_Validation_Exception',
                         "Invalid exclusion validation rule, must be either an array or a numeric rule");
        }
    }
    
    /**
     * Helper function for _validate_number()
     */
    private function _convert_number(&$num)
    {
        if (!empty($this->_params['only_integer']))
            $num = (int)$num;
        else
            $num = (float)$num;
    }
}