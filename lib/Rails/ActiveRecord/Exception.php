<?php
class ActiveRecord_Exception extends Rails_Exception
{
    private $_stmt;
    
    private $_values;
    
    public function set_stmt(PDOStatement $stmt)
    {
        $this->_stmt = $stmt;
    }
    
    public function set_values(array $values)
    {
        $this->_values = $values;
    }
    
    public function get_message()
    {
        // $einfo = $this->_stmt->errorInfo();
        // if ($einfo[2])
            // $error = '[' . $einfo[0] . '][' . $einfo[1] . '] ' . $einfo[2];
        // else
            // $error = '';
        
        $msg = parent::getMessage();
        // if ($error)
        $msg .= "\nQuery: " . $this->_stmt->queryString;
        
        if ($this->_values) {
            $msg .= "\nValues: " . var_export($this->_values, true);
        }
        
        return $msg;
    }
}