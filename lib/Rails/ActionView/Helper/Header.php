<?php
trait ActionView_Helper_Header
{
    public function stylesheet_link_tag($url, array $attrs = array())
    {
        $attrs['href'] = $this->_parse_url($url, '/stylesheets/', 'css');
        empty($attrs['type']) && $attrs['type'] = 'text/css';
        empty($attrs['rel'])  && $attrs['rel']  = 'stylesheet';
        return $this->tag('link', $attrs);
    }
    
    public function javascript_include_tag($url, array $attrs = array())
    {
        $attrs['src'] = $this->_parse_url($url, '/javascripts/', 'js');
        empty($attrs['type']) && $attrs['type'] = 'text/javascript';
        return $this->tag('script', $attrs, true) . '</script>';
    }
    
    private function _parse_url($url, $default_base_url, $ext)
    {
        $base_url = Rails::application()->config('app');
        $base_url = $base_url['base_url'];
        
        if (strpos($url, '/') === 0) {
            $url = $base_url . $url;
        } elseif (strpos($url, 'http') !== 0 && strpos($url, 'www') !== 0) {
            $url = $base_url . $default_base_url . $url . '.' . $ext;
        }
        return $url;
    }
}