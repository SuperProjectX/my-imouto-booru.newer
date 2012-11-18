<?php
class ActionController_Response_View extends ActionController_Response_Base
{
    private $_xml;
    
    public function _render_view()
    {
        try {
            ActionView::include_helpers();
            
            Rails::load_klass('ActionView_Template');
            
            $this->_renderer = new ActionView_Template($this->_params, $this->_params['layout']);
            
            $locals = Rails::application()->controller()->vars();
            
            if (!empty($this->_params['is_xml'])) {
                Rails::load_klass('Rails_Xml');
                Rails::load_klass('ActionView_Xml');
                $this->_xml = new ActionView_Xml();
                $locals->xml = $this->_xml;
            }
            
            $this->_renderer->set_locals($locals);
            
            $this->_renderer->render_content();
        } catch (ActionView_Renderer_Exception $e) {
            if (Rails::application()->dispatcher()->action_ran())
                Rails::raise('Rails_ActionDispatch_Exception_ViewNotFound',
                             "View for '%s#%s' not found.",
                             array($this->router()->route()->action, $this->router()->route()->controller));
            else
                Rails::raise('Rails_ActionDispatch_Exception_ActionNotFound',
                             "Action '%s' not found for controller '%s'.",
                             array($this->router()->route()->action, $this->router()->route()->controller));
        }
    }
    
    public function _print_view()
    {
        if (!empty($this->_params['is_xml']))
            return $this->_xml->output();
        else
            return $this->_renderer->get_buffer_and_clean();
    }
}