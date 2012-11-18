<?php
class ActionController_Response_Headers
{
    private $_http_status = 200;
    
    private $_headers = array();
    
    private $_headers_sent = false;
    
    private $_content_type;
    
    public function status()
    {
        return $this->_http_status;
    }
    
    public function set_status($status)
    {
        if (is_int($status) || ctype_digit((string)$status))
            $this->_http_status = (int)$status;
        elseif (is_string($status))
            $this->_http_status = $status;
        else
            Rails::raise('InvalidArgumentException', "%s accepts string, %s passed.", [__METHOD__, gettype($value)]);
        return $this;
    }
    
    public function set_redirect($url, $status = 302)
    {
        if (!is_string($url))
            Rails::raise('InvalidArgumentException', "%s accepts string as first parameter, %s passed.", [__METHOD__, gettype($value)]);
        elseif (!is_int($status) && !is_string($status))
            Rails::raise('InvalidArgumentException', "%s accepts string or int as second parameter, %s passed.", [__METHOD__, gettype($status)]);
        
        $this->set_status($status)->set('Location', $url);
        $this->_http_status = $status;
        return $this;
    }
    
    public function set($name, $value = null)
    {
        if (!is_string($name))
            Rails::raise('InvalidArgumentException', "First argument for %s must be a string, %s passed.", [__METHOD__, gettype($value)]);
        elseif (!is_null($value) && !is_string($value) && !is_int($value))
            Rails::raise('InvalidArgumentException', "%s accepts null, string or int as second argument, %s passed.", [__METHOD__, gettype($value)]);
        
        if (strpos($name, 'Content-type') === 0) {
            if ($value !== null) {
                $name = $name . $value;
            }
            $this->set_content_type($name);
        } elseif ($name == 'status') {
            $this->set_status($value);
        } elseif (strpos($name, 'HTTP/') === 0) {
            $this->set_status($name);
        } else {
            if ($value === null) {
                if (count(explode(':', $name)) < 2)
                    Rails::raise('ActionController_Response_Headers_Exception', "%s is not a valid header", $name);
                $this->_headers[] = $name;
            } else {
                $this->_headers[$name] = $value;
            }
        }
        return $this;
    }
    
    public function send()
    {
        if ($this->_headers_sent) {
            Rails::raise('ActionController_Response_Headers_Exception',
                         "Headers have already been sent, can't send them twice.");
        }
        
        if (!$this->_content_type)
            $this->_set_default_content_type();
        header($this->_content_type);
        
        foreach ($this->_headers as $name => $value) {
            if (!is_int($name))
                $value = $name . ': ' . $value;
            header($value);
        }
        
        if ($this->_http_status !== 200)
            header('HTTP/1.1 ' . $this->_http_status);
        
        $this->_headers_sent = true;
    }
    
    public function set_content_type($content_type)
    {
        if (!is_string($content_type))
            Rails::raise('InvalidArgumentException',
                         "Content type must be a string, %s passed", gettype($content_type));
        
        switch ($content_type) {
            case 'html':
                $content_type = 'text/html';
                break;
            
            case 'json':
                $content_type = 'application/json';
                break;
            
            case 'xml':
                $content_type = 'application/xml';
                break;
        }
        
        if (strpos($content_type, 'Content-type:') !== 0)
            $content_type = 'Content-type: ' . $content_type;
        
        $this->_content_type = $content_type;
    }
    
    private function _set_default_content_type()
    {
        $this->set_content_type('text/html; charset='.Rails::application()->config('app', 'encoding'));
    }
}