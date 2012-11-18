<?php
# TODO
class ActionController_Response_Partial extends ActionController_Response_Base
{
    public function _render_view()
    {
        $params = [$this->_params['partial']];
        if (isset($this->_params['locals']))
            $params = array_merge($params, [$this->_params['locals']]);
        
        # Include helpers.
        ActionView::include_helpers();
        # Create a template so we can call render_partial.
        # This shouldn't be done this way.
        Rails::load_klass('ActionView_Template');
        $template = new ActionView_Template([]);
        
        $this->_body = call_user_func_array([$template, 'render_partial'], $params);
    }
    
    public function _print_view()
    {
        return $this->_body;
    }
}