<?php
trait ActionView_Helper_FormTag
{
    /**
     * Passing an empty value as $action_url will cause the form
     * to omit the "action" attribute, causing the form to be
     * submitted to the current uri.
     *
     * To avoid passing an empty array for $attrs,
     * pass a Closure as second argument and it
     * will be taken as $block.
     *
     * Likewise, passing Closure as first argument
     * (meaning the form will be submitted to the current url)
     * will work too, instead of passing an empty value as 
     * $action_url.
     */
    public function form_tag($action_url = null, $attrs = [], Closure $block = null)
    {
        if (func_num_args() == 1 && $action_url instanceof Closure) {
            $block = $action_url;
            $action_url = null;
        } elseif ($attrs instanceof Closure) {
            $block = $attrs;
            $attrs = [];
        }
        
        if (!$block instanceof Closure)
            Rails::raise('ActionView_Helper_Exception', "One of the arguments for %s must be a Closure.", __METHOD__);
        
        empty($attrs['method']) && $attrs['method'] = 'post';
        
        # Check special attribute 'multipart'.
        if (!empty($attrs['multipart'])) {
            $attrs['enctype'] = 'multipart/form-data';
            unset($attrs['multipart']);
        }
        
        if ($action_url)
            $attrs['action'] = ActionController::url_for($action_url);
        
        ob_start();
        $block();
        return $this->content_tag('form', ob_get_clean(), $attrs);
    }
    
    public function submit_tag($value, array $attrs = array())
    {
        $attrs['type'] = 'submit';
        $attrs['value'] = $value;
        !isset($attrs['name']) && $attrs['name'] = 'commit';
        return $this->tag('input', $attrs);
    }
    
    public function text_field_tag($name, $value, array $attrs = array())
    {
        return $this->_form_field_tag('text', $name, $value, $attrs);
    }
    
    public function hidden_field_tag($name, $value, array $attrs = array())
    {
        return $this->_form_field_tag('hidden', $name, $value, $attrs);
    }
    
    public function check_box_tag($name, $value, array $attrs = array())
    {
        return $this->_form_field_tag('checkbox', $name, $value, $attrs);
    }
    
    public function text_area_tag($name, $value, array $attrs = array())
    {
        if (isset($attrs['size']) && is_int(strpos($attrs['size'], 'x'))) {
            list($attrs['cols'], $attrs['rows']) = explode('x', $attrs['size']);
            unset($attrs['size']);
        }
        return $this->_form_field_tag('textarea', $name, $value, $attrs, true);
    }
    
    public function radio_button_tag($name, $value, $checked = false, array $attrs = array())
    {
        if ($checked)
            $attrs['checked'] = 'checked';
        return $this->_form_field_tag('radio', $name, $value, $attrs);
    }
    
    # $options may be closure, collection or an array of name => values.
    public function select_tag($name, $options, array $attrs = array())
    {
        # This is found also in Form::select()
        if (!is_string($options)) {
            if (is_array($options) && is_indexed($options) && count($options) == 2)
                list ($options, $value) = $options;
            else
                $value = null;
            $options = $this->options_for_select($options, $value);
        }
        
        $attrs['value'] = $attrs['type'] = null;
        
        $select_tag = $this->_form_field_tag('select', $name, $options, $attrs, true);
        return $select_tag;
    }
    
    /**
     * Note: For options to recognize the $tag_value, it must be identical to the option's value.
     */
    public function options_for_select($options, $tag_value = null)
    {
        # New feature: accept anonymous functions that will return options.
        if ($options instanceof Closure) {
            $options = $options();
        # New feature: accept collection as option in index 0, in index 1 the option name and in index 2 the value
        # which are the properties of the models that will be used.
        # Example: array($users, 'name', 'id')
        # If option_name  or value have () (i.e. 'pretty_name()'), means it's a function.
        } elseif (is_array($options) && count($options) == 3 && is_indexed($options) && $options[0] instanceof ActiveRecord_Collection) {
            // list($models, $opts_params) = $options;
            list($models, $option_name, $value) = $options;
            
            if (is_int(strpos($option_name, '()'))) {
                $option_name = substr($option_name, 0, -2);
                $foption = true;
            } else
                $foption = false;
            
            if (is_int(strpos($value, '()'))) {
                $value = substr($value, 0, -2);
                $fvalue = true;
            } else
                $fvalue = false;
            
            $options = array();
            foreach ($models as $m) {
                $opt = $foption ? $m->$option_name() : $m->$option_name;
                $val = $fvalue ? $m->$value() : $m->$value;
                $options[$opt] = $val;
            }
        }
        $tag_value = (string)$tag_value;
        // ctype_digit((string)$tag_value) && $tag_value = (string)$tag_value;
        $tags = array();
        foreach ($options as $name => $value) {
            $value = (string)$value;
            // ctype_digit((string)$value) && $value = (string)$value;
            $tags[] = $this->_form_field_tag('option', null, $name, array('selected' => $value === $tag_value ? '1' : '', 'id' => '', 'value' => $value), true);
        }
        
        return implode("\n", $tags);
    }
    
    private function _form_field_tag($field_type, $name = null, $value, array $attrs = array(), $content_tag = false)
    {
        // vde($name);
        !isset($attrs['id']) && $attrs['id'] = trim(str_replace(['[', ']'], '_', $name), '_');
        $name && $attrs['name'] = $name;
        $value = (string)$value;
        
        if ($content_tag) {
            return $this->content_tag($field_type, $value, $attrs);
        } else {
            $attrs['type'] = $field_type;
            $value !== '' && $attrs['value'] = $value;
            return $this->tag('input', $attrs);
        }
    }
}