<?php
/**
 * This class should be part of Router,
 * because if no route is matched,
 * a routing exception is raised.
 */
class ActionController_UrlFor
{
    /**
     * Initial url.
     */
    private $_init_url;
    
    /**
     * Final url.
     */
    private $_url;
    
    private $_params;
    
    private $_token;
    
    private $_route;
    
    private $_anchor;
    
    public function __construct($params)
    {
        if (is_string($params)) {
            $init_url = $params;
            $params = array();
        } elseif (is_array($params)) {
            if (isset($params['controller']) && isset($params['action']))
                $init_url = $params['controller'] . '#' . $params['action'];
            elseif (isset($params['controller']) && !isset($params['action']))
                $init_url = $params['controller'] . '#index';
            elseif (!isset($params['controller']) && isset($params['action']))
                $init_url = Rails::application()->dispatcher()->router()->route()->controller . '#' . $params['action'];
            else
                $init_url = array_shift($params);
            unset($params['controller'], $params['action']);
        } else {
            Rails::raise('InvalidArgumentException', "Argument must be either string or array, %s passed", gettype($params));
        }
        
        if (is_array($init_url)) {
            $params = $init_url;
            $init_url = array_shift($params);
        }
        
        $this->_init_url = $init_url;
        $this->_params = $params;
        
        // if (!empty($params['compare_current_uri'])) {
            // if 
        // }
        
        if ($init_url === 'root') {
            $this->_url = Rails_ActionDispatch_Router::ROOT_URL;
        } elseif (is_int(strpos($init_url, '#'))) {
            $this->_parse_token($init_url);
            $this->_find_route_for_token();
            if ($this->_route) {
                $this->_build_route_url();
            } else {
                Rails::raise('Rails_ActionDispatch_Router_Exception', 'No route matches %s', $init_url);
            }
        } else {
            $this->_url = $init_url;
        }
        
        $this->_build_params();
        $this->_build_url();
        
        unset($this->_init_url, $this->_params, $this->_token, $this->_route, $this->_anchor);
    }
    
    /**
     * Returns final url.
     */
    public function url()
    {
        return $this->_url;
    }
    
    private function _parse_token($url)
    {
        $token = new Rails_UrlToken($url);
        $this->_token = implode('#', $token->parts());
    }
    
    private function _find_route_for_token()
    {
        foreach (Rails::application()->dispatcher()->router()->routes() as $route) {
            if ($this->_url = $route->match_with_token($this->_token, $this->_params)) {
                $this->_route = $route;
                break;
            }
            // if ($this->_token === $route->to || $this->_token === $route->alias()) {
                // if ($this->_url = $route->build_url($this->_params, false)) {
                    // $this->_route = $route;
                    // break;                    
                // }
            // } elseif (!$route->to) {
                // list($this->_params['controller'], $this->_params['action']) = explode('#', $this->_token);
                // if ($this->_url = $route->build_url($this->_params, false)) {
                    // $this->_route = $route;
                    // break;                    
                // }
            // }
        }
    }
    
    private function _build_basic_url_for_token()
    {
        $this->_url = '/' . str_replace('#', '/', $this->_token);
    }
    
    private function _build_route_url()
    {
        $this->_url = $this->_route->build_url($this->_params);
        
        $this->_params = array_intersect_key($this->_params, $this->_route->remaining_params());
    }
    
    private function _build_params()
    {
        if (isset($this->_params['anchor'])) {
            $this->_anchor = '#' . $this->_params['anchor'];
            unset($this->_params['anchor']);
        }
        $query = http_build_query($this->_params);
        $this->_params = $query ? '?' . $query : '';
    }
    
    private function _build_url()
    {
        if (strpos($this->_url, '/') === 0)
            $leading = $this->_base_url();
        else
            $leading = rtrim(Rails::application()->dispatcher()->request()->path(), '/') . '/';
        
        $url = $leading . $this->_url . $this->_params . $this->_anchor;
        
        $this->_url = $url;
    }
    
    private function _base_url()
    {
        $config = Rails::application()->config('app');
        return $config['base_url'];
    }
}