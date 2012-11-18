<?php
final class Rails
{
    /**
     * Rails_Application instance.
     */
    static private $_app;
    
    static private $_config;
    
    static private $_raised_exception;
    
    static private $_application_name;
    
    /**
     * These response_params will be set
     * in case an error ocurred.
     */
    static private $_response_params;
    
    /**
     * raise() will fill this with extra params for
     * exceptions, and used by init_application().
     */
    static private $_tmp_response_params = array();
    
    /**
     * Path to the framework.
     */
    static private $_root;
    
    /**
     * Boots system (load files, etc).
     *
     * @see _default_config()
     */
    public static function boot(array $config = array())
    {
        self::$_root = dirname(__FILE__);
        
        if (!defined('RAILS_ROOT'))
            die("<br /><strong>Rails error:</strong> Required constant <strong>RAILS_ROOT</strong> is not defined.");
        try {
            self::$_config = array_merge(self::_default_config(), $config);
            include self::$_root.'/Functions.php';
            self::_set_initial_config();
            self::_load_files();
        } catch (Exception $e) {
            self::_report_exception($e);
        }
    }
    
    public static function load_application($application_name)
    {
        try {
            self::$_application_name = $application_name;
            self::$_app = new $application_name();
            
            if (!self::$_app instanceof Rails_Application) {
                self::raise('Rails_Application_Exception', 'Application must be child of Rails_Application');
            }
            self::$_app->init();
        } catch (Exception $e) {
            self::_report_exception($e);
        }
    }
    
    public static function init_application()
    {
        ActionView::clean_buffers();
        ob_start();
        try {
            self::$_app->initialize();
        } catch (Exception $e) {
            self::_report_exception($e);
        }
    }
    
    public static function load_class($class_name)
    {
        if (class_exists($class_name, false) || interface_exists($class_name, false))
            return;
        
        $class_file = RAILS_ROOT . '/lib/' . str_replace('_', '/', $class_name) . '.php';
        
        if (!is_file($class_file))
            self::raise('Rails_Exception', "Couldn't find file %s for class %s.", [$class_file, $class_name]);
        
        require $class_file;
        
        if (!class_exists($class_name, false))
            self::raise('Rails_Exception', "File %s doesn't contain class %s.", [$class_file, $class_name]);
    }
    
    /**
     * This function is supposed to be used only by Rails.
     */
    public static function load_klass($class_name, $throw_e = true)
    {
        if (class_exists($class_name, false) || interface_exists($class_name, false))
            return true;
        
        $base_path = self::$_root . '/';
        
        $class_path = str_replace('_', '/', $class_name);
        strpos($class_path, 'Rails/') === 0 && $class_path = substr($class_path, 6);
        $class_file = $base_path . $class_path . '.php';
        
        if (!is_file($class_file)) {
            if ($throw_e)
                self::raise('Rails_Exception', "Couldn't find file %s for class %s.", [$class_file, $class_name]);
            else
                return false;
        }
        
        require $class_file;
        
        if (!class_exists($class_name, false)) {
            if ($throw_e)
                self::raise('Rails_Exception', "File %s doesn't contain class %s.", [$class_file, $class_name]);
            else
                return false;
        }
        return true;
    }
    
    public static function application()
    {
        return self::$_app;
    }
    
    public static function config($idx = null)
    {
        if ($idx)
            return self::$_config[$idx];
        else
            return self::$_config;
    }
    
    static public function lower_to_camel($lower)
    {
        if (is_int(strpos($lower, '_')))
            $name = str_replace(' ', '', ucwords(str_replace('_', ' ', $lower)));
        else
            $name = ucfirst($lower);

        return $name;
    }
    
    static public function camel_to_lower($camel)
    {
        return strtolower(trim(preg_replace('/([A-Z])/', '_\1', $camel), '_'));
    }
    
    /**
     * Centrailize raise of Exceptions to avoid
     * calling Rails::load_(c|k)lass() for every specific exception class.
     */
    public static function raise($exception, $msg, $params = [], array $extra_params = [])
    {
        if (!self::load_klass($exception, false))
            self::load_class($exception);
            
        self::$_raised_exception = true;
        
        if ($params) {
            !is_array($params) && $params = array($params);
            array_unshift($params, $msg);
            $msg = call_user_func_array('sprintf', $params);
        }
        
        self::$_tmp_response_params = $extra_params;
        
        throw new $exception($msg);
    }
    
    public static function raised_exception()
    {
        return self::$_raised_exception;
    }
    
    public static function error_handler($errno, $errstr, $errfile, $errline)
    {
        switch ($errno) {
            case E_WARNING:
                $errtype = 'Warning';
                break;

            case E_NOTICE:
                $errtype = 'Notice';
                break;
            
            case E_RECOVERABLE_ERROR:
                $errtype = 'Catchable fatal error';
                break;

            case E_USER_NOTICE:
                $errtype = 'User Notice';
                break;
                
            case E_USER_WARNING:
                $errtype = 'User Warning';
                break;

            default:
                $errtype = '[ErrNo '.$errno.']';
                break;
        }
        $errfile = substr($errfile, strlen(RAILS_ROOT) + 1);
        self::raise('Rails_Exception', '[PHP error] %s: %s ', [$errtype, $errstr], ['php_error' => true]);
    }
    
    public static function response_params()
    {
        return self::$_response_params;
    }
    
    public static function application_name()
    {
        return self::$_application_name;
    }
    
    public static function configure(array $config = array())
    {
        self::$_config = array_merge(self::$_config, $config);
    }
    
    public static function log($contents, $append = true)
    {
        if (!is_string($contents)) {
            ob_start();
            var_dump($contents);
            $contents = ob_get_clean();
        }
        
        $contents .= "\n;\n";
        
        $file = self::config('logs_path') . '/rails.log';
        file_put_contents($file, $contents, ($append ? FILE_APPEND : 0));
    }
    
    public static function log_error($err, $ignore_config = true)
    {
        if (Rails::application()->config('app', 'log_errors') || $ignore_config) {
            $log_file = Rails::application()->config('app', 'error_log');
            
            if (!is_file($log_file)) {
                $fh = fopen($log_file, 'a');
                fclose($fh);
            }
            
            $route = Rails::application()->dispatcher()->request() ? ' ' . Rails::application()->dispatcher()->request()->fullpath() : '[no route]';
            $log  = date('[d-M-Y H:i:s T]') . $route . "\n";
            $log .= $err;
            $log  = trim($log);
            $log .= "\n\n";
            
            if (($max_size = Rails::application()->config('app', 'log_errors_max_len')) && (filesize($log_file)/1024) >= $max_size) {
                file_put_contents($log_file, $log);
            } else {
                file_put_contents($log_file, $log, FILE_APPEND);
            }
        }
    }
    
    public static function root()
    {
        return self::$_root;
    }
    
    private static function _report_exception(Exception $e)
    {
        ActionView::clean_buffers();
        
        $params = self::$_tmp_response_params;
        
        if (!isset($params['status']))
            $params['status'] = 500;
        
        self::application()->dispatcher()->response()->headers()->set_status($params['status']);
        self::load_klass('ActionController_Response_Base');
        self::load_klass('ActionController_Response_Error');
        $renderer = new ActionController_Response_Error($e, $params);
        self::application()->dispatcher()->response()->headers()->set_content_type('html');
        self::application()->dispatcher()->response()->body($renderer->render_view()->get_contents());
        self::application()->dispatcher()->respond();
        exit;
    }
    
    private static function _set_initial_config()
    {
        set_include_path(implode(PATH_SEPARATOR, array(
            RAILS_ROOT . '/lib',
            get_include_path(),
        )));
        
        if (self::config('rails_autoloader'))
            spl_autoload_register('Rails::load_class');
    }
    
    private static function _load_files()
    {
        $classes = [
            'Rails_Exception',
            'Rails_Application',
            'ActionView',
            'Rails_ActionDispatch',
            'ActionController_Response',
            'ActionController_Response_Headers',
            'ActiveRecord',
            'ActiveRecord_Collection',
            'ActiveRecord_Base',
            'Rails_ActionDispatch_Http_Parameters',
            'Rails_ActionDispatch_Http_Request',
            'Rails_ActionDispatch_Router',
            'Rails_ActionDispatch_Router_Route',
            'Rails_UrlToken',
            'ActionController',
            'ActionController_Base',
            'ActionController_Cookies',
        ];
        
        foreach ($classes as $class)
            self::load_klass($class);
        
        require self::$_config['application_filename'];
    }
    
    private static function _default_config()
    {
        $config = [
            'application_filename' => RAILS_ROOT . '/config/application.php',
            'environments_path'    => RAILS_ROOT . '/config/environments',
            'routes_filename'      => RAILS_ROOT . '/config/routes.php',
            'app_path'             => RAILS_ROOT . '/app',
            'public_path'          => RAILS_ROOT . '/public',
            'table_schema_path'    => RAILS_ROOT . '/db/tables',
            'logs_path'            => RAILS_ROOT . '/log',
            'rails_admin_files'    => self::$_root . '/Admin',
            'rails_admin_custom'   => RAILS_ROOT . '/scripts/rails_admin',
            'rails_autoloader'     => true
        ];
        $config['controllers_path'] = $config['app_path'] . '/controllers';
        $config['views_path']       = $config['app_path'] . '/views';
        $config['layouts_path']     = $config['app_path'] . '/views/layouts';
        $config['helpers_path']     = $config['app_path'] . '/helpers';
        $config['models_path']      = $config['app_path'] . '/models';
        $config['app_helper_filename'] = $config['helpers_path'] . '/application_helper.php';
        $config['application_controller_filename'] = $config['controllers_path'] . '/application_controller.php';
        
        return $config;
    }
}