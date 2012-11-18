<?php
trait ActionView_Helper_Tag
{
    public function tag($name, array $options = array(), $open = false, $escape = false)
    {
        return '<' . $name . ' ' . $this->_options($options, $escape) . ($open ? '>' : ' />');
    }
    
    public function content_tag($name, $content, array $options = array(), $escape = false)
    {
        if ($content instanceof Closure) {
            $content = $content($this->view());
        }
        return $this->_content_tag_string($name, $content, $options, $escape);
    }
    
    protected function _options(array $options = array(), $escape = false)
    {
        $opts = array();
        
        foreach ($options as $opt => $val) {
            if ((string)$val === '')
                continue;
            $escape && $val = htmlentities($val);
            $opts[] = $opt . '="' . $val . '"';
        }
        return implode(' ', $opts);
    }
    
    protected function _parse_size(&$attrs)
    {
        if (is_int(strpos($attrs['size'], 'x'))) {
            list ($attrs['width'], $attrs['height']) = explode('x', $attrs['size']);
            unset($attrs['size']);
        }
    }
    
    private function _content_tag_string($name, $content, array $options, $escape = false)
    {
        return '<' . $name . ' ' . $this->_options($options) . '>' . ($escape ? $this->h($content) : $content) . '</' . $name . '>';
    }
}