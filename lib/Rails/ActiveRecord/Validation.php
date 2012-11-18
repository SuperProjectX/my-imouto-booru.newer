<?php
/**
 * A validation could be made with a Closure, which will receive
 * an argument (the value of the property) and must return true
 * and only true if the validation is passed.
 *
 * 'name' => array(
 *     'length' => '5..',
 *     'format' => function($name) { ... return true; },
 * )
 */
class ActiveRecord_Validation extends Rails_Validation
{
    private
        $_model,
    
        $_action,
    
        $_property,
    
        /**
         * Helps for validations that could have
         * different messages (e.g. length (minimum, maximum, is))
         */
        $_error_message_type = 'default',
        
        $_error_message,
    
        $_continue_validation;
    
    public function set_params($action, ActiveRecord_Base $model, $property)
    {
        $this->_model    = $model;
        $this->_action   = $action;
        $this->_property = $property;
    }
    
    public function validate()
    {
        if (current($this->_params) instanceof Closure)
            $this->_run_closure();
        else
            $this->_check_conditions();
        
        if ($this->_continue_validation)
            parent::validate();
        
        return $this;
    }
    
    public function set_error_message()
    {
        if (isset($this->_params['base_message']))
            $this->_model->errors()->add_to_base($this->_params['base_message']);
        else {
            $this->_set_error_message();
            $this->_model->errors()->add($this->_property, $this->_error_message);
        }
    }
    
    protected function _validate_number()
    {
        if (isset($this->_params['allow_null']) && $this->_model->{$this->_property} === null)
            return true;
        else
            return parent::_validate_number();
    }
    
    protected function _validate_length()
    {
        if (isset($this->_params['allow_null']) && $this->_model->{$this->_property} === null)
            return true;
        elseif (isset($this->_params['allow_blank']) && $this->_model->{$this->_property} === '')
            return true;
        else
            return parent::_validate_length();
    }
    
    protected function _validate_uniqueness()
    {
        $cn = get_class($this->_model);
        $sql = ['conditions' => ['`'.$this->_property.'` = ? AND id != ?', $this->_model->{$this->_property}, $this->_model->id]];
        return !((bool)$cn::find_first($sql));
    }
    
    protected function _validate_presence()
    {
        return !empty($this->_model->{$this->_property});
    }
    
    protected function _validate_confirmation()
    {
        if ($this->_model->{$this->_property} === null)
            return true;
        
        $confirm_property = $this->_property . '_confirmation';
        return !empty($this->_model->$confirm_property);
    }
    
    protected function _validate_acceptance()
    {
        return !empty($this->_model->{$this->_property});
    }
    
    private function _run_closure()
    {
        $closure = current($this->_params);
        if ($closure($this->_model->{$this->_property}) === true) {
            $this->_success = true;
        }
    }
    
    private function _check_conditions()
    {
        if (!isset($this->_params['on']))
            $this->_params['on'] = 'save';
        $this->_run_on();
        
        if (isset($this->_params['if']))
            $this->_run_if();
    }
    
    private function _run_on()
    {
        if ($this->_params['on'] == $this->_action)
            $this->_continue_validation = true;
        else
            $this->_success = true;
    }
    
    private function _run_if()
    {
        if (is_array($this->_params['if'])) {
            foreach ($this->_params['if'] as $cond => $params) {
                if ($params instanceof Closure) {
                    if ($params() !== true) {
                        $this->_success = true;
                        $this->_continue_validation = false;
                        return;
                    }
                } else {
                    switch ($cond) {
                        case 'property_exists':
                            if ($this->_model->$params === null) {
                                $this->_success = true;
                                $this->_continue_validation = false;
                                return;
                            }
                            break;
                    }
                }
            }
        } else {
            Rails::raise('ActiveRecord_Validation_Exception', "Validation condition must be an array, %s passed", gettype($this->_params['if']));
        }
        
        $this->_continue_validation = true;
    }
    
    private function _set_error_message()
    {
        $message = '';
        $this->_define_error_message_type();
        
        if ($this->_error_message_type != 'default') {
            if (isset($this->_params[$this->_error_message_type]))
                $message = $this->_params[$this->_error_message_type];
        }
        if (!$message)
            $message = $this->_error_message();
        $this->_error_message = $message;
    }
    
    private function _define_error_message_type()
    {
        switch ($this->_type) {
            case 'length':
                if ($this->_result == -1)
                    $msg_type = 'too_short';
                elseif ($this->_result == 1)
                    $msg_type = 'too_long';
                else
                    $msg_type = 'wrong_length';
                break;
            default:
                $msg_type = 'default';
                break;
        }
        $this->_error_message_type = $msg_type;
    }
    
    private function _error_message()
    {
        switch ($this->_type) {
            case 'number':
            case 'length':
                if ($this->_result == 2 && (!empty($this->_params['even']) || !empty($this->_params['odd']))) {
                    $type = !empty($this->_params['even']) ? "even" : "odd";
                    $msg = "must be an ".$type." number";
                } elseif (is_string($this->_rule)) {
                    $parts = array_filter(explode('..', $this->_rule));
                    
                    switch (true) {
                        case isset($parts[0]) && isset($parts[1]):
                            $diff = $this->_result == -1 ? "short" : "big";
                            $msg = "is too ".$diff." (must be between ".$parts[0]." and ".$parts[1]." characters)";
                            break;
                        case !isset($parts[0]) && isset($parts[1]):
                            $msg = "is too long (maximum is ".$parts[1]." characters)";
                            break;
                        case isset($parts[0]) && !isset($parts[1]):
                            $msg = "is too short (min is ".$parts[0]." characters)";
                            break;
                        default:
                            Rails::raise("ActiveRecord_Validation_Exception", "Invalid number/length rule: %s", $this->_rule);
                    }
                    
                    // if (isset($parts[0]) && isset($parts[1])) {
                        // $diff = $this->_result == -1 ? "short" : "big";
                        // $msg = "is too ".$diff." (must be between ".$parts[0]." and ".$parts[1]." characters)";
                    // } elseif (!isset($parts[0]) && isset($parts[1])) {
                        // $msg = "is too long (maximum is ".$parts[1]." characters)";
                    // } elseif (isset($parts[0]) && !isset($parts[1])) {
                        // $msg = "is too short (min is ".$parts[0]." characters)";
                    // } else {
                        // Rails::raise("ActiveRecord_Validation_Exception", "Invalid number/length rule: %s", $this->_rule);
                    // }
                } else {
                    $msg = "is the wrong length (should be ".$this->_rule." characters)";
                }
                break;
            
            case 'blank':
                $msg = 'cannot be blank';
                break;
            
            case 'uniqueness':
                $msg = 'must be unique';
                break;
            
            default:
                $msg = 'is invalid';
                break;
        }
        
        return $msg;
    }
}