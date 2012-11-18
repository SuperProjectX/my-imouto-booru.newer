<?php
/**
 * This class is expected to be extended by the ApplicationController
 * class, and that one to be extended by the current controller's class.
 * There can't be other extensions for now as they might cause
 * unexpected results.
 *
 * ApplicationController class will be instantiated so its _init()
 * method is called, because it is supposed to be called always, regardless
 * the actual controller.
 */
class ActionController_Base extends ActionController
{
    protected $_layout;

    private
        /**
         * Variables set to the controller itself will be stored
         * here through __set(), and will be passed to the view.
         *
         * @var stdClass
         * @see vars()
         */
        $_vars,
        
        /**
         * By default, requests respond to html, xml and json.
         *
         * @see _respond_to()
         */
        $_respond_to_formats = ['html', 'xml', 'json'],
         
        /**
         * Extra actions to run according to request format.
         *
         * This accepts the following params:
         * Null: Nothing has been set (default).
         * True: Can respond to format, no action needed.
         * Closure: Can respond to format, run Closure.
         * False: Can't respond to format. Render Nothing with 406.
         */
        $_respond_action,
        
        /**
         * Default variable to respond with.
         *
         * @see _respond_with()
         */
        $_respond_with,

        /**
         * Stores the render parameters.
         *
         * @see render_params()
         * @see _render()
         * @see _redirect_to()
         * @see _set_response_params()
         */
        $_response_params = [],
        
        $_response_extra_params = [];
    
    /**
     * Children classes shouldn't override __construct(),
     * they should declare _init() instead.
     *
     * Application controller will be instantiated and shouldn't
     * have vars set.
     */
    public function __construct()
    {
        if (get_called_class() != 'ApplicationController') {
            $this->_set_default_layout();
            $this->_vars = new stdClass();
            $this->_init();
        }
    }
    
    public function __set($prop, $val)
    {
        $this->_vars->$prop = $val;
    }
    
    public function __get($prop)
    {
        if (!array_key_exists($prop, (array)$this->_vars))
            Rails::raise('ActionController_Base_Exception', "Trying to get undefined property '%s'.", $prop);
        return $this->_vars->$prop;
    }
    
    /**
     * Returns _layout.
     */
    public function layout()
    {
        if (func_num_args())
            Rails::raise('ActionController_Base_Exception', 'Method \'layout\' shouldn\'t receive any parameter.');
        return $this->_layout;
    }
    
    public function response_params()
    {
        return $this->_response_params;
    }
    
    public function vars()
    {
        return $this->_vars;
    }
    
    /**
     * Shortcut
     */
    public function I18n()
    {
        return Rails::application()->I18n();
    }

    public function run_request_action()
    {
        $action = Rails::application()->dispatcher()->router()->route()->action;
        
        if (!$this->_action_method_exists($action) && !$this->_view_file_exists()) {
            Rails::raise('ActionController_Exception_UnknownAction',
                         "The action '%s' could not be found for %s",
                         array($action, get_called_class()),
                         array('skip_all_info' => true, 'status' => 404));
        }
        
        $this->_run_filters('before');
        /**
         * Check if response params where set by the
         * before filters.
         */
        if ($this->response_params())
            return;
        
        if ($this->_action_method_exists($action))
            $this->_run_action($action);
        
        $this->_run_filters('after');
        $this->_create_response_body();
    }
    
    /**
     * This method was created so it can be extended in
     * the controllers with the solely purpose of
     * customizing the handle of Exceptions.
     */
    protected function _run_action($action)
    {
        $this->$action();
    }
    
    protected function _init()
    {
    }
    
    /**
     * Respond to format
     *
     * Sets to which formats the action will respond.
     * It accepts a list of methods that will be called if the
     * request matches the format.
     *
     * Example:
     *   $this->_respond_to(array(
     *      'html',
     *      'xml' => array(
     *          '_some_method' => array($param_1, $param_2),
     *          '_render'      => array(array('xml' => $obj), array('status' => 403))
     *      )
     *  ));
     *
     * Note: The way this function receives its parameters is because we can't use Closures,
     * due to the protected visibility of the methods such as _render().
     *
     * In the case above, it's stated that the action is able to respond to html and xml.
     * In the case of an html request, no further action is needed to respond; therefore,
     * we just list the 'html' format there.
     * In the case of an xml request, the controller will call _some_method($param_1, $param_2)
     * then it will call the _render() method, giving it the variable with which it will respond
     * (an ActiveRecord_Base object, an array, etc), and setting the status to 403.
     * Any request with a format not specified here will be responded with a 406 HTTP status code.
     *
     * By default all requests respond to xml, json and html. If the action receives a json
     * request for example, but no data is set to respond with, the dispatcher will look for
     * the .$format.php file in the views (in this case, .json.php). If the file is missing,
     * which actually is expected to happen, a Dispatcher_TemplateMissing exception will be
     * thrown.
     *
     * @see _respond_with()
     */
    protected function _respond_to($responses)
    {
        $format = $this->request()->format();
        
        // $can_respond = false;
        
        foreach ($responses as $fmt => $action) {
            if (is_int($fmt)) {
                $fmt = $action;
                $action = null;
            }
            
            if ($fmt !== $format)
                continue;
            // vde($action);
            
            if ($action) {
                if (!$action instanceof Closure) {
                    // vde('n');
                    // $methods();
                // } else {
                    Rails::raise('InvalidArgumentException', 'Only closure can be passed to respond_to(), %s passed', gettype($action));
                }
                $action();
                // vde('n2');
            } else {
                $action = true;
            }
            
            $this->_respond_action = $action;
            return;
            
            // $can_respond = true;
            // break;
        }
        
        /**
         * The request format is not acceptable.
         * Set to render nothing with 406 status.
         */
        $this->_render(array('nothing' => true), array('status' => 406));
        
        // $this->_respond_action = false;
        
        // /**
         // * The request format is not acceptable.
         // * Set to render nothing with 406 status.
         // */
        // if (!$can_respond) {
            // $this->_render(array('nothing' => true), array('status' => 406));
        // }
        
        // $this->_respond_to_formats = $responses;
        /**
         * _respond_to may be called before the action is ran,
         * we have to let the dispatcher know response_params are
         * set, so it won't run the action.
         */
        // $this->run_respond_to();
        // if (!$this->_response_params)
            // $this->_response_params = [true];
    }
    
    protected function _respond_with($var)
    {
        $this->_respond_with = $var;
    }
    
    /**
     * Sets layout value.
     */
    protected function _layout($value)
    {
        $this->_layout = $value;
    }
    
    /**
     // * Made static 
     * @return array
     */
    protected function _filters()
    {
        return [];
    }
    
    protected function _render($type, array $params = array())
    {
        if (is_array($type)) {
            if (isset($type['layout'])) {
                $this->_layout($type['layout']);
                unset($type['layout']);
            }
        } else {
            # Maybe to be able to do $this->_render('nothing'); ?
            $type = [$type => true];
        }
        if ($type)
            $this->_set_response_params($type, $params);
    }
    
    /**
     * Sets a redirection.
     * TODO: accept other variables than just a string.
     */
    protected function _redirect_to($redirect_params, array $params = array())
    {
        $this->_set_response_params(array('redirect' => $redirect_params), $params);
    }
    
    /**
     * For now we're only expecting one controller that extends ApplicationController,
     * that extends ActionController_Base.
     * This could change in the future (using Reflections) so there could be more classes
     * extending down to ApplicationController > ActionController_Base
     */
    protected function _run_filters($type)
    {
        $filters = array_merge_recursive((new ApplicationController())->_filters(), $this->_filters());
        
        if (isset($filters[$type])) {
            /**
             * Here we have to filter duped methods. We can't use array_unique
             * because the the methods could be like ['method_name', 'only' => [ actions ... ]]
             * and that will generate "Array to string conversion" error.
             */
            $ran_methods = [];
            foreach ($filters[$type] as $params) {
                if (($method = $this->_can_run_filter_method($params, $type)) && !in_array($method, $ran_methods)) {
                    $this->$method();
                    $ran_methods[] = $method;
                }
            }
        }
    }
    
    private function _action_method_exists($action)
    {
        static $method_exists;
        if ($method_exists !== null)
            return $method_exists;
        
        $method_exists = false;
        $refl = new ReflectionClass($this);
        foreach ($refl->getMethods(ReflectionMethod::IS_PUBLIC) as $method) {
            if ($method->getName() == $action && $method->getDeclaringClass()->getName() == get_called_class()) {
                $method_exists = true;
                break;
            }
        }
        return $method_exists;
    }
    
    /**
     * If the method for the requested action doesn't exist in
     * the controller, it's checked if the view file exists.
     */
    private function _view_file_exists()
    {
        $route = Rails::application()->dispatcher()->router()->route();
        # Build a simple path for the view file, as there's no support for
        # stuff like modules.
        # Note that the extension is PHP, there's no support for different
        # request formats.
        $base_path = Rails::config('views_path');
        $view_path = $base_path . '/' . $route->path() . '.php';
        return is_file($view_path);
    }
    
    private function _set_default_layout()
    {
        $this->_layout(Rails::application()->config('actionview', 'layout'));
    }
    
    private function _can_run_filter_method($params, $filter_type)
    {
        if (is_array($params)) {
            $method = array_shift($params);
        } else {
            $method = $params;
            $params = [];
        }
        
        if (is_callable(array($this, $method))) {
            if (isset($params['only']) && !in_array($this->params()->action, $params['only'])) {
                return;
            } elseif (isset($params['except']) && in_array($this->params()->action, $params['except'])) {
                return;
            }
            return $method;
        } else
            Rails::raise('ActionController_Base_Exception', "Unable to call method %s::%s() for %s filter", array(get_class(), $method, $filter_type));
    }
    
    private function _create_response_body()
    {
        if (!$this->_response_params) {
            $route = Rails::application()->dispatcher()->router()->route();
            $this->_response_params = ['action' => $route->controller . '#' . $route->action];
        }
        
        $render_type = key($this->_response_params);
        $main_param = array_shift($this->_response_params);

        Rails::load_klass('ActionController_Response_Base');
        
        if (isset($this->_response_extra_params['status']))
            $this->response()->headers()->set_status($this->_response_extra_params['status']);
        
        $class = null;

        switch ($render_type) {
            case 'action':
                is_bool(strpos($main_param, '#')) && $main_param = '#' . $main_param;
                
                $path_params = [];
                $class = 'ActionController_Response_View';
                
                if ($this->request()->format() == 'html') {
                    $ext = 'php';
                } else {
                    $ext = $this->request()->format() . '.php';
                    $this->response()->headers()->set_content_type($this->request()->format());
                }
                
                $path_params['template'] = $main_param;
                $path_params['format'] = $ext;
                $path_params['layout'] = $this->_layout;
                
                if ($this->request()->format() == 'xml')
                    $this->_response_params['is_xml'] = true;
                $this->_response_params = array_merge($this->_response_params, $path_params);
                break;
            
            case 'redirect':
                $url = ActionController::url_for($main_param);
                $this->response()->headers()->set_redirect($url);
                break;
                
            case 'partial':
                $class = 'ActionController_Response_Partial';
                $this->_response_params['partial'] = $main_param;
                break;
            
            case 'json':
                $this->response()->headers()->set_content_type('application/json');
                $this->_response_params = $main_param;
                $class = "ActionController_Response_Json";
                break;
            
            case 'xml':
                $class = "ActionController_Response_Xml";
                break;
            
            case 'text':
                $this->response()->body($main_param);
                break;
            
            case 'inline':
                $this->_response_params['code'] = $main_param;
                $class = "ActionController_Response_Inline";
                break;
            
            case 'nothing':
                break;
            
            default:
                Rails::raise('ActionController_Response_Exception', "Invalid action render type '%s'", $render_type);
                break;
        }
        
        if ($class) {
            Rails::load_klass('ActionView_Base');
            Rails::load_klass($class);
            $responder = new $class($this->_response_params);
            $responder->render_view();
            $this->response()->body($responder->get_contents());
        }
    }
    
    /**
     * This function is accessed by _render() and _redirect_to() only.
     */
    private function _set_response_params($type, $params)
    {
        if ($this->_response_params)
            Rails::raise('ActionController_Base_Exception_DoubleRender', 'Can only render or redirect once per action.');
        $this->_response_params = $type;
        $this->_response_extra_params = $params;
    }
}