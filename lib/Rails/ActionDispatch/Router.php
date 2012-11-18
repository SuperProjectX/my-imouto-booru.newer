<?php
class Rails_ActionDispatch_Router
{
    const ROOT_URL = '/';
    
    const ROOT_DEFAULT_TO = 'welcome#index';
    
    const ADMIN_DEFAULT_TO = 'admin#';
    
    /**
     * Array holding Rails_Router_Route objects.
     */
    private $_routes = array();
    
    /**
     * Absolute request path.
     *
     * @see _find_request_path()
     */
    private $_request_path;
    
    /**
     * Temporary helds default values when calling scope().
     */
    private $_scope_params = array();
    
    /**
     * Rails_Router_Route instance that holds the root route.
     */
    private $_root;
    
    /**
     * Rails_Router_Route instance that holds the admin route.
     */
    private $_admin;
    
    /**
     * Rails_Router_Route instance that matched the request.
     */
    private $_route;
    
    /**
     * Another way to set a scope, instead of using scope():
     *  Call set_scope($params), set all your routes, then
     *  call end_scope() to clear the scope.
     *
     * @see _create_route()
     * @see _add_scope_params()
     */
    public function set_scope($params)
    {
        $this->_scope_params = $params;
    }
    
    public function end_scope()
    {
        $this->_scope_params = array();
    }
    
    public function scope(array $params, Closure $routes_closure)
    {
        $this->set_scope($params);
        $routes_closure();
        $this->end_scope();
    }
    
    public function match($url, $to = null, array $params = array())
    {
        if (!$params && is_array($to)) {
            $params = $to;
            $to = null;
        }
        
        $this->_create_and_add_route($url, $to, $params);
    }
    
    public function get($url, $to = null, array $params = array())
    {
        if (is_array($to)) {
            $params = $to;
            $to = null;
        }
        $params['via'] = 'get';
        $this->_create_and_add_route($url, $to, $params);
    }
    
    public function post($url, $to = null, array $params = array())
    {
        if (is_array($to)) {
            $params = $to;
            $to = null;
        }
        $params['via'] = 'post';
        $this->_create_and_add_route($url, $to, $params);
    }
    
    public function root($to)
    {
        $this->_root = $this->_create_route(self::ROOT_URL, $to, array());
    }
    
    public function find_route()
    {
        $this->_import_routes();
        $this->_match_routes();
    }
    
    public function route()
    {
        return $this->_route;
    }
    
    public function routes()
    {
        return $this->_routes;
    }
    
    private function _import_routes()
    {
        $config = Rails::config();
        $routes_file = $config['routes_filename'];
        require $routes_file;
        if (!$this->_root)
            $this->root(self::ROOT_DEFAULT_TO);
        $this->_create_admin_route();
    }
    
    private function _match_routes()
    {
        $request_path = $this->_find_request_path();
        $request_method = $_SERVER['REQUEST_METHOD'];
        
        if ($this->_root->match($request_path, $request_method)) {
            $this->_route = $this->_root;
        } elseif ($this->_admin->match($request_path, $request_method)) {
            $this->_route = $this->_admin;
        } else {
            foreach ($this->_routes as $route) {
                if ($route->match($request_path, $request_method)) {
                    $this->_route = $route;
                    break;
                }
            }
        }
        
        if (!$this->_route)
            Rails::raise('Rails_ActionDispatch_Router_Exception', 'No route matches [%s] "%s"', array(
                         Rails::application()->dispatcher()->request()->method(),
                         Rails::application()->dispatcher()->request()->path()
                         ), array('skip_all_info' => true, 'status' => 404));
    }
    
    private function _create_and_add_route($url, $to, array $params)
    {
        $this->_routes[] = $this->_create_route($url, $to, $params);
    }
    
    private function _create_route($url, $to, array $params)
    {
        $this->_add_scope_params($params);
        
        $route = new Rails_ActionDispatch_Router_Route($url, $to, $params);
        $route->build();
        return $route;
    }
    
    public function _create_admin_route()
    {
        $base_url = Rails::application()->config('app', 'rails_admin_url');
        $this->_admin = $this->_create_route($base_url . '(/:action)', self::ADMIN_DEFAULT_TO, array('rails_admin' => true, 'defaults' => array('controller' => 'admin')));
    }
    
    /**
     * Centralizing the addition of scope params.
     * This is called by _create_route().
     */
    private function _add_scope_params(array &$params)
    {
        !empty($this->_scope_params) && $params = array_merge($this->_scope_params, $params);
    }
    
    private function _find_request_path()
    {
        if (!$this->_request_path) {
            preg_match('/([^?]+)/', $_SERVER['REQUEST_URI'], $m);
            $this->_request_path = $m[1];
        }
        return $this->_request_path;
    }
}