<?php
class ActionController_Response_Json extends ActionController_Response_Base
{
    private $_json;
    
    private $_header_params;
    
    public function __construct($json)
    {
        $this->_json = $json;
    }
    
    public function _render_view()
    {
        if ($this->_json instanceof ActiveRecord_Base)
            $this->_json = $this->_json->to_json();
        elseif (isset($this->_json['json'])) {
            if (!is_string($this->_json['json']))
                $this->_json = json_encode($this->_json['json']);
            else
                $this->_json = $this->_json['json'];
        } elseif (!is_string($this->_json))
            $this->_json = json_encode($this->_json);
    }
    
    public function _print_view()
    {
        return $this->_json;
    }
}