<?php
trait ActionView_Helper_JavaScript
{
    public function link_to_function($link, $function, array $attrs = array())
    {
        $attrs['href'] = '#';
        $function = trim($function);
        if (strpos($function, -1) != ';')
            $function .= ';';
        $function .= ' return false;';
        $attrs['onclick'] = $function;
        
        return $this->content_tag('a', $link, $attrs);
    }
    
    public function button_to_function($name, $function, array $attrs = array())
    {
        $attrs['href'] = '#';
        $function = trim($function);
        if (strpos($function, -1) != ';')
            $function .= ';';
        $function .= ' return false;';
        $attrs['onclick'] = $function;
        $attrs['type'] = 'button';
        $attrs['value'] = $name;
        return $this->tag('input', $attrs);
    }
}