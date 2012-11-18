<?php
trait ActionView_Helper_Html
{
    private $_form_attrs;
    
    public function link_to($link, $url_params, array $attrs = array())
    {
        $url_to = $this->_parse_url_params($url_params);
        $onclick = '';
        
        if (isset($attrs['method'])) {
            $onclick = "var f = document.createElement('form'); f.style.display = 'none'; this.parentNode.appendChild(f); f.method = '".$attrs['method']."'; f.action = this.href;f.submit();return false;";
        }
        
        if (isset($attrs['confirm'])) {
            if (!$onclick)
                $onclick = "if (!confirm('".$attrs['confirm']."')) return false;";
            else
                $onclick = 'if (confirm(\''.$attrs['confirm'].'\')) {'.$onclick.'}; return false;';
            unset($attrs['confirm']);
        }
        
        if ($onclick)
            $attrs['onclick'] = $onclick;
        
        $attrs['href'] = $url_to;
        
        return $this->content_tag('a', $link, $attrs);
    }
    
    public function link_to_if($condition, $link, $url_params, array $attrs = array())
    {
        if ($condition)
            return $this->link_to($link, $url_params, $attrs);
        else
            return $link;
    }
    
    public function auto_discovery_link_tag($type = 'rss', $url_params = null, array $attrs = array())
    {
        if (!$url_params) {
            $url_params = Rails::application()->dispatcher()->router()->route()->controller . '#' .
                          Rails::application()->dispatcher()->router()->route()->action;
        }
        $attrs['href'] = $this->_parse_url_params($url_params);
        
        empty($attrs['type'])  && $attrs['type']  = 'application/' . strtolower($type) . '+xml';
        empty($attrs['title']) && $attrs['title'] = strtoupper($type);
        
        return $this->tag('link', $attrs);
    }
    
    public function image_tag($source, array $attrs = array())
    {
        if (is_bool(strpos($source, '/')))
            $source = '/images/' . $source;
        if (!isset($attrs['alt']))
            $attrs['alt'] = ucfirst(pathinfo($source, PATHINFO_FILENAME));
        if (isset($attrs['size']))
            $this->_parse_size($attrs);
        $attrs['src'] = $source;
        return $this->tag('img', $attrs);
    }
    
    public function mail_to($address, $name = null, array $options = array())
    {
        if ($name === null) {
            $name = $address;
            if (isset($options['replace_at']))
                $name = str_replace('@', $options['replace_at'], $address);
            if (isset($options['replace_dot']))
                $name = str_replace('.', $options['replace_dot'], $address);
        }
        $encode = isset($options['encode']) ? $options['encode'] : false;
        
        if ($encode == 'hex') {
            $address = $this->hex_encode($address);
        }
        
        $address_options = array('subject', 'body', 'cc', 'bcc');
        
        $query = array_intersect_key($options, array_fill_keys($address_options, null));
        if ($query)
            $query = '?' . http_build_query($query);
        else
            $query = '';
        $address .= $query;
        
        $attrs = array_diff_key($options, $address_options, array_fill_keys(array('replace_at', 'replace_dot', 'encode'), null));
        $attrs['href'] = 'mailto:' . $address;
        
        $tag = $this->content_tag('a', $name, $attrs);
        
        if ($encode = 'javascript') {
            $tag = "document.write('" . $tag . "');";
            return $this->wrap_js('eval(decodeURIComponent(\'' . $this->hex_encode($tag) . '\'))');
        } else
            return $tag;
    }
}