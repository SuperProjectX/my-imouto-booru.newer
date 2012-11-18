<?php
class Rails_ActionDispatch
{
    /**
     * ActionController_Response instance.
     */
    private $_response;
    
    private $_parameters;
    
    private $_request;
    
    private $_router;
    
    private $_action_ran = false;
    
    private $_action_dispatched = false;
    
    private $_view;
    
    private $_responded = false;
    
    public function init()
    {
        $this->_response = new ActionController_Response();
        $this->_response->_init();
    }
    
    public function load_request_and_params()
    {
        if (!$this->_parameters) {
            $this->_parameters = new Rails_ActionDispatch_Http_Parameters();
            $this->_request    = new Rails_ActionDispatch_Http_Request();
        } else {
            Rails::raise('Rails_ActionDispatch_Exception', "Can't call init() more than once");
        }
    }
    
    public function find_route()
    {
        $this->_router = new Rails_ActionDispatch_Router();
        $this->_router->find_route();
        $this->_route_vars_to_params();
    }
    
    // public function action_ran()
    // {
        // return $this->_action_ran;
    // }
    
    public function router()
    {
        return $this->_router;
    }
    
    public function parameters()
    {
        return $this->_parameters;
    }
    
    public function request()
    {
        return $this->_request;
    }
    
    public function controller()
    {
        return Rails_Application::application()->controller();
    }
    
    public function response()
    {
        return $this->_response;
    }
    
    public function respond()
    {
        if ($this->_responded && !Rails::response_params()) {
            Rails::raise('Rails_ActionDispatch_Exception', "Can't respond to request more than once");
        } else {
            // $this->_response->_set_params($this->_get_response_params());
            $this->_response->_respond();
            $this->_responded = true;
        }
    }
    
    private function _route_vars_to_params()
    {
        $vars = $this->_router->route()->vars();
        unset($vars['controller'], $vars['action']);
        foreach ($vars as $name => $val) {
            if ($this->_parameters->$name === null)
                $this->_parameters->add($name, $val);
        }
    }
    
    // private function _get_response_params()
    // {
        // return Rails::response_params() ?: ($this->controller() ? $this->controller()->response_params() : []);
    // }
    
    private function _action_name()
    {
        return $this->router()->route()->action;
    }
    
    /**
     * Actually runs the action.
     *
     * @see ActionController_Base::run_action()
     */
    // private function _run_action()
    // {
        // $resp = $this->_app()->controller()->run_action($this->_action_name());
        // if (is_int($resp) && $resp >= 100) {
            // # TODO
        // }
    // }
    
    private function _action_exists()
    {
        $controller = Rails::application()->controller();
        return method_exists($controller, $this->_action_name()) && is_callable(array($controller, $this->_action_name()));
    }
    
    private function _app()
    {
        return Rails_Application::application();
    }
}