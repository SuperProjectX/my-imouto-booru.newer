<?php
/**
 * This class will accept two kind of parameters to decide what to render:
 * Inline code, specifically made for Inline render type, and the normal
 * parameters.
 */
class ActionView_Template extends ActionView_Base
{
    protected $_filename;
    
    /**
     * Layout filename.
     */
    protected $_layout;
    
    protected $_layout_name;
    
    private $_params;
    
    private $_template_token;
    
    private $_initial_ob_level;
    
    /**
     * Used by Inline responder.
     */
    private $_inline_code;
    
    /**
     * To render for first time, render() must be
     * called, and this will be set to true.
     * Next times render() is called, it will need
     * parameters in order to work.
     */
    private $_init_rendered = false;
    
    public function __construct(array $params, $layout = false, array $locals = array())
    {
        $this->_params = $params;
        if (isset($this->_params['inline_code'])) {
            $this->_inline_code = $this->_params['inline_code'];
            unset($this->_params['inline_code']);
        }
        $this->_determine_view_file();
        
        if ($layout) {
            $this->_layout_name = $layout;
            $this->_resolve_layout_filename();
        }
        $locals && $this->set_locals($locals);
    }
    
    public function render_content()
    {
        $this->_set_initial_ob_level();
        
        if (!$this->_init_rendered) {
            ob_start();
            $this->_init_rendered = true;
            $this->_init_render();
            $this->_buffer = ob_get_clean();
            
            if (!$this->_validate_ob_level()) {
                $status = ob_get_status();
                Rails::raise('ActionView_Template_Exception_OutputLeaked',
                             'Buffer level: %s; File: %s<br />Topmost buffer\'s contents: <br />%s', array(
                             $status['level'], substr($this->_filename, strlen(RAILS_ROOT) + 1), htmlentities(ob_get_clean())));
            }
        }
        return $this;
    }
    
    /**
     * Currently accepting:
     * render(array('action' => 'show'))
     * render('show')
     * render(array('template' => 'post/show'))
     * render('post/show')
     */
    public function render($params)
    {
        if (is_string($params)) {
            if (is_bool(strpos($params, '/'))) {
                $params = array('action' => $params);
            } else {
                $params = array('template' => $params);
            }
        } elseif (!is_array($params)) {
            Rails::raise('InvalidArgumentException', 'Argument must be either string or array, %s passed', gettype($params));
        }
        $template = new ActionView_Template($params);
        return $template->render_content()->get_buffer_and_clean();
    }
    
    public function yield($name = null)
    {
        if (!func_num_args())
            return $this->_buffer;
        else
            return parent::yield($name);
    }
    
    /**
     * Finally prints all the view.
     */
    public function get_buffer_and_clean()
    {
        $buffer = $this->_buffer;
        $this->_buffer = null;
        return $buffer;
    }
    
    public function t($params)
    {
        if (is_string($params))
            $name = $params;
        else
            $name = current($params);
        
        if (strpos($name, '.') === 0) {
            if (is_int(strpos($this->_filename, Rails::config('views_path')))) {
                $parts = array();
                $path = substr($this->_filename, strlen(Rails::config('views_path')) + 1, strlen(pathinfo($this->_filename, PATHINFO_BASENAME)) * -1)
                        . pathinfo($this->_filename, PATHINFO_FILENAME);
                foreach (explode('/', $path) as $part)
                    $parts[] = ltrim($part, '_');
                $name = implode('.', $parts) . $name;
                
                if (is_string($params))
                    $params = $name;
                else
                    $params[key($params)] = $name;
            }
        }
        
        return parent::t($params);
    }
    
    protected function _init_render()
    {
        if (!$this->_inline_code && !is_file($this->_filename)) {
            $params = array();
            if ($this->_template_token && isset($this->_params['format'])) {
                $msg = "Missing template '%s' with [format=>%s]. Searched in: %s";
                $params[] = $this->_template_token;
                $params[] = $this->_params['format'];
                $params[] = Rails::config('views_path');
            } else {
                $msg = "Missing file '%s'.";
                $params[] = $this->_filename;
            }
            Rails::raise('ActionView_Template_Exception_TemplateMissing', $msg, $params);
        }
        
        if ($this->_layout) {
            if (!is_file($this->_layout))
                Rails::raise('ActionView_Template_Exception_LayoutMissing',
                             "Missing layout '%s'. Layout path: %s",
                             array($this->_layout_name, Rails::config('layouts_path')));
            ob_start();
            if ($this->_inline_code)
                eval($this->_inline_code);
            else
                require $this->_filename;
            $this->_buffer = ob_get_clean();
            require $this->_layout;
        } else {
            if ($this->_inline_code)
                eval($this->_inline_code);
            else
                require $this->_filename;
        }
    }
    
    private function _set_initial_ob_level()
    {
        $status = ob_get_status();
        $this->_initial_ob_level = $status['level'];
    }
    
    private function _validate_ob_level()
    {
        $status = ob_get_status();
        return $this->_initial_ob_level == $status['level'];
    }
    
    private function _resolve_layout_filename()
    {
        $this->_layout = Rails::config('layouts_path') . '/' . $this->_layout_name . '.php';
    }
    
    private function _determine_view_file()
    {
        $base_path = Rails::config('views_path');
        
        if (isset($this->_params['filename'])) {
            $this->_filename = $this->_params['filename'];
        } elseif (isset($this->_params['template'])) {
            $template = $this->_params['template'];
            if (is_int(strpos($template, '.php')))
                $template = strlen($template, 0, -4);
            
            if (is_int(strpos($template, '#')))
                $this->_template_token = implode('/', (new Rails_UrlToken($template))->parts());
            else
                $this->_template_token = str_replace('#', '/', $template);
            
            $this->_filename = $base_path . '/' . $this->_template_token . '.php';
        } else {
            $route = Rails::application()->dispatcher()->router()->route();
            $this->_filename = $base_path . '/' . $route->path($this->_params);
            
            if (isset($this->_params['action'])) {
                if (is_int(strpos($this->_params['action'], '.php')))
                    $action = strlen($this->_params['action'], 0, -4);
                else
                    $action = $this->_params['action'];
            } else {
                $action = $route->action;
            }
            
            $controller = $route->controller;
            $this->_template_token = $controller . '#' . $action;
        }
    }
}