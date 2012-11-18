<?php
trait ActionView_Helper_Text
{
    // protected $_name = 'TextHelper';
    
    public function cycle()
    {
        static $vars;
        static $cycle = 0;
        
        $args = func_get_args();
        
        # Clear vars if null was passed.
        if (count($args) == 1 && $args[0] === null) {
            $vars = null;
            $cycle = 0;
            return;
        # Reset cycle if new options were given.
        } elseif ($vars && $vars !== $args) {
            $vars = null;
            $cycle = 0;
        }
        
        if (empty($vars))
            $vars = $args;
        if ($cycle > count($vars) - 1)
            $cycle = 0;
        
        $value = $vars[$cycle];
        $cycle++;
        return $value;
    }
}