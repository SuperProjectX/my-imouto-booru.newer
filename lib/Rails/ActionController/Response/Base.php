<?php
abstract class ActionController_Response_Base extends ActionController_Response
{
    public function __construct(array $params = array())
    {
        $this->_params = $params;
    }
    
    public function render_view()
    {
        $this->_render_view();
        return $this;
    }
    
    public function get_contents()
    {
        return $this->_print_view();
    }
    
    abstract public function _render_view();
    
    abstract public function _print_view();
}