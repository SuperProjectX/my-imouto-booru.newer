<?php
class ActionController_Response_Redirect extends ActionController_Response_Base
{
    private $_redirect_params;
    
    private $_header_params;
    
    public function __construct(array $redirect_params)
    {
        // vde($redirect_params);
        # Todo: not sure what will be in second index
        # for now, only http status.
        list($this->_redirect_params, $this->_header_params) = $redirect_params;
    }
    
    protected function _render_view()
    {
        $url = ActionController::url_for($this->_redirect_params);
        Rails::application()->dispatcher()->response()->headers()->set_redirect($url);
    }
    
    protected function _print_view()
    {
    }
}