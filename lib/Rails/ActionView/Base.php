<?php
/**
 * Base class for layouts, templates and partials.
 */
abstract class ActionView_Base extends ActionView
{
    /**
     * Local variables passed.
     * This could be either an array (partials) or an stdClass
     * (layouts and templates). They're accessed through __get();
     */
    private $_locals;
    
    public function __get($prop)
    {
        if ($this->_locals) {
            if (!$this->isset_local($prop))
                Rails::raise('ActionView_Base_Exception', "Undefined local '%s'", $prop);
            elseif ($this->_locals instanceof stdClass) {
                return $this->_locals->$prop;
            } else {
                return $this->_locals[$prop];
            }
        }
    }
    
    public function __set($prop, $val)
    {
        if ($this->_locals) {
            if ($this->_locals instanceof stdClass)
                $this->_locals->$prop = $val;
            else
                $this->_locals[$prop] = $val;
        }
    }
    
    /**
     * Same function can be found in ActionView_Helper class.
     */
    public function __call($method, $params)
    {
        if ($helper = ActionView::find_helper_for($method)) {
            $helper->set_view($this);
            return call_user_func_array(array($helper, $method), $params);
        } else {
            Rails::raise('ActionView_Base_Exception', "Called to undefined method/helper '%s'.", $method);
        }
    }
    
    public function __isset($prop)
    {
        return $this->isset_local($prop);
    }
    
    public function isset_local($name)
    {
        if ($this->_locals instanceof stdClass)
            return property_exists($this->_locals, $name);
        elseif (is_array($this->_locals))
            return array_key_exists($name, $this->_locals);
    }
    
    public function I18n()
    {
        return Rails::application()->I18n();
    }
    
    public function t($name)
    {
        return $this->I18n()->t($name);
    }
    
    /**
     * This is meant to be a way to check if
     * there are content_for awaiting to be ended.
     */
    public function active_content_for()
    {
        return self::$_content_for_names;
    }
    
    public function set_locals($locals)
    {
        if (!is_array($locals) && !$locals instanceof stdClass)
            throw new ActionView_Base_Exception('Locals must be either an array or an instance of stdClass, ' . gettype($locals) . ' passed.');
        $this->_locals = $locals;
    }
    
    public function params()
    {
        return Rails::application()->dispatcher()->parameters();
    }
    
    public function request()
    {
        return Rails::application()->dispatcher()->request();
    }
    
    public function render_partial($name, array $locals = array())
    {
        Rails::load_klass('ActionView_Partial');
        
        $ctrlr_name = $this->request()->controller();
        if (!isset($locals[$ctrlr_name]) && $this->isset_local($ctrlr_name))
            $locals[$ctrlr_name] = $this->__get($ctrlr_name);
        if (!isset($locals[$name]) && $this->isset_local($name))
            $locals[$name] = $this->__get($name);
        
        $base_path = Rails::config('views_path');
        
        if (is_int(strpos($name, '/'))) {
            $pos      = strrpos($name, '/');
            $name     = substr_replace($name, '/_', $pos, 1) . '.php';
            $filename = $base_path . '/' . $name;
        } else
            $filename = $base_path . '/' . $this->request()->controller() . '/_' . $name . '.php';
        
        $partial = new ActionView_Partial(array('filename' => $filename), false, $locals);
        
        return $partial->render_content();
    }
}