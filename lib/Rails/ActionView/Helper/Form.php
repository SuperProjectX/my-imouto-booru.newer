<?php
trait ActionView_Helper_Form
{
    public function text_field($model, $property, array $attrs = array())
    {
        return $this->_form_field('text', $model, $property, $attrs);
    }
    
    public function hidden_field($model, $property, array $attrs = array())
    {
        return $this->_form_field('hidden', $model, $property, $attrs);
    }
    
    public function password_field($model, $property, array $attrs = array())
    {
        return $this->_form_field('password', $model, $property, $attrs);
    }
    
    public function check_box($model, $property, array $attrs = array(), $checked_value = '1', $unchecked_value = '0')
    {
        if ($this->_get_model_property($model, $property))
            $attrs['checked'] = 'checked';
        
        $attrs['value'] = $checked_value;
        
        $hidden = $this->tag('input', array('type' => 'hidden', 'name' => $model.'['.$property.']', 'value' => $unchecked_value));
        
        $check_box = $this->_form_field('checkbox', $model, $property, $attrs);
        
        return $hidden . "\n" . $check_box;
    }
    
    public function text_area($model, $property, array $attrs = array())
    {
        if (isset($attrs['size']) && is_int(strpos($attrs['size'], 'x'))) {
            list($attrs['cols'], $attrs['rows']) = explode('x', $attrs['size']);
            unset($attrs['size']);
        }
        return $this->_form_field('textarea', $model, $property, $attrs, true);
    }
    
    public function select($model, $property, $options, array $attrs = array())
    {
        if (!is_string($options)) {
            $value = $this->_get_model_property($model, $property);
            $options = $this->options_for_select($options, $value);
        }
        
        $attrs['value'] = $options;
        
        return $this->_form_field('select', $model, $property, $attrs, true);
    }
    
    public function radio_button($model, $property, $checked = false, array $attrs = array())
    {
        $checked && $attrs['checked'] = 'checked';
        return $this->_form_field('hidden', $model, $property, $attrs);
    }
    
    public function file_field($model, $property, array $attrs = array())
    {
        return $this->_form_field('file', $model, $property, $attrs);
    }
    
    private function _form_field($field_type, $model, $property, array $attrs = array(), $content_tag = false)
    {
        $value = isset($attrs['value']) ? $attrs['value'] : $this->_get_model_property($model, $property, $attrs);
        $attrs['name'] = $model.'['.$property.']';
        
        if (!isset($attrs['id']))
            $attrs['id'] = $model . '_' . $property;
        
        if ($content_tag) {
            unset($attrs['value']);
            return $this->content_tag($field_type, $value, $attrs);
        } else {
            $attrs['type'] = $field_type;
            if ($value !== '')
                $attrs['value'] = $value;
            return $this->tag('input', $attrs);
        }
    }
    
    private function _get_model_property($model, $property)
    {
        $value = '';
        $vars  = Rails::application()->dispatcher()->controller()->vars();
        if (!empty($vars->$model)) {
            $mdl = $vars->$model;
            if ($mdl && null !== $mdl->$property)
                $value = (string)$mdl->$property;
        }
        return $value;
    }
}