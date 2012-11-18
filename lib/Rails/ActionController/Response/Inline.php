<?php
# TODO
class ActionController_Response_Inline extends ActionController_Response_Base
{
    private $_template;
    
    public function _render_view()
    {
        $code = '?>' . $this->_params['code'] . '<?php';
        
        # Include helpers.
        ActionView::include_helpers();
        $layout = !empty($this->_params['layout']) ? $this->_params['layout'] : false;
        # Create a template so we can call render_inline;
        Rails::load_klass('ActionView_Template');
        $this->_template = new ActionView_Template(['inline_code' => $code], $layout);
        $this->_template->render_content();
    }
    
    public function _print_view()
    {
        return $this->_template->yield();;
    }
}