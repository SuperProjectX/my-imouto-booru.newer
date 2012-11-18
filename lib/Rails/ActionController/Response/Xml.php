<?php
class ActionController_Response_Xml extends ActionController_Response_Base
{
    private $_xml;
    
    public function _render_view()
    {
        Rails::load_klass('Rails_Xml');
        vde(__METHOD__, __FILE__);
        $this->_xml = new Rails_Xml($this->_params);
    }
    
    public function _print_view()
    {
        // return $this->_json;
    }
}