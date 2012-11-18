<?php
class ActionController_Response extends Rails_ActionDispatch
{
    private
        /**
         * ActionController_Cookies instance
         */
        $_cookies,
    
        /**
         * ActionController_Response_Headers instance
         */
        $_headers,
    
        /**
         * Response params taken from Rails or ActionController.
         *
         * @see _set_params()
         */
        $_params,
        
        $_body,
        
        $_view;
    
    public function cookies()
    {
        if (!$this->_cookies)
            $this->_cookies = new ActionController_Cookies();
        return $this->_cookies;
    }
    
    public function headers($name = null, $value = null)
    {
        if (func_num_args())
            $this->_headers->set($name, $value);
        return $this->_headers;
    }
    
    public function body($value = null)
    {
        if (func_num_args()) {
            if ($this->_body !== null)
                Rails::raise("ActionController_Response_Exception", "Body has already been set.");
            else
                $this->_body = $value;
        }
    }
    
    protected function _init()
    {
        $this->_headers = new ActionController_Response_Headers();
    }
    
    protected function _respond()
    {
        $this->_send_headers();
        echo $this->_body;
        $this->_body = null;
    }
    
    protected function _set_params(array $params)
    {
        if ($params) {
            if (!isset($params[0]) || !is_array($params[0])) {
                $param = current($params);
                $params[0] = array($param);
            }
            count($params) != 2 && $params[1] = array();
        }
        $this->_params = $params;
        return $this;
    }
    
    private function _send_headers()
    {
        $this->headers()->send();
        $this->cookies()->set();
    }
}