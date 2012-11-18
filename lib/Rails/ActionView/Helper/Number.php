<?php
trait ActionView_Helper_Number 
{
    // protected $_name = 'NumberHelper';
    
    public function number_to_human_size($number, array $options = array())
    { 
        $size = $number / 1024; 
        if ($size < 1024){ 
            $size = number_format($size, 1); 
            $size .= ' KB'; 
        } else { 
            if($size / 1024 < 1024){ 
                    $size = number_format($size / 1024, 1); 
                    $size .= ' MB'; 
            } else if ($size / 1024 / 1024 < 1024) { 
                    $size = number_format($size / 1024 / 1024, 1); 
                    $size .= ' GB'; 
            }    
        } 
        return $size; 
    }

}