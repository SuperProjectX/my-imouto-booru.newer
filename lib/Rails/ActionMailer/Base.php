<?php
abstract class ActionMailer_Base
{
    protected
        $_to,
        $_subject,
        $_body,
        $_headers,
        $_params;
    
    protected function _default_options()
    {
        return [];
    }
    
    protected function _mail()
    {
        if (!is_array($this->_to))
            $this->_to = [$this->_to];
        
        foreach ($this->_to as $recipient)
            mail($recipient, $this->_subject, $this->_body, $this->_headers, $this->_params);
    }
}