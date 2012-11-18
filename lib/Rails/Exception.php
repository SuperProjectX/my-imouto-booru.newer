<?php
class Rails_Exception extends Exception
{
    /**
     * Title to show instead of the class name.
     * Stetic purposes only.
     */
    protected $_title;
    
    private $_error_html;
    
    // public function __construct($message = '', $code = 0, Exception $previous = null)
    // {
        // parent::__construct($message, $code, $previous);
        // $this->_report();
    // }
    

    
    // public function report()
    // {

    // }
    public function title()
    {
        return $this->_title;
    }
    
    public function error_html()
    {
        !$this->_error_html && $this->_build_html();
        return $this->_error_html;
    }
    
    private function _build_html()
    {
        $error_name = get_called_class();
        $base_dir = RAILS_ROOT . DIRECTORY_SEPARATOR;
        
        $trace = str_replace("\n", '<br />', $this->getTraceAsString());
        $trace = str_replace($base_dir, '', $trace);
        $file  = substr($this->getFile(), strlen($base_dir));
        
        $html = '';
        $html .= '<h1>'.$error_name.' raised</h1>';
        $html .= '<pre>'.$this->getMessage().'<br />';
        $html .= $file.' ('.$this->getLine().')</pre>';
        $html .= 'RAILS_ROOT: ' . RAILS_ROOT;
        $html .= '<h3>Trace</h3>';
        $html .= '<pre>' . $trace . '</pre>';
        $this->_error_html = $html;
    }
}