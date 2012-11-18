<?php
abstract class Rails_Application
{
    const CONFIG_ERROR_SHOW_STACKTRACE  = 0;
    
    const CONFIG_ERROR_SHOW_500_PAGE    = 1;
    
    const CONFIG_ERROR_DO_NOTHING       = 2;
    
    static private $_instance;
    
    private $_router;
    
    private $_dispatcher;
    
    /**
     * Rails_I18n instance.
     */
    private $_I18n;
    
    private $_config;
    
    /**
     * Actual controller instance.
     */
    private $_controller;
    
    static public function application()
    {
        return self::$_instance;
    }
    
    static public function boot()
    {
        Rails::load_application(get_called_class());
    }
    
    static public function init_application()
    {
        Rails::init_application();
    }
    
    static public function run()
    {
        self::boot();
        self::init_application();
    }
    
    public function __construct()
    {
        self::$_instance = $this;
        $this->_set_initial_config();
    }
    
    /**
     * This will be called by Rails after creating the instance.
     * The reason is that these functions could throw exceptions,
     * that need the configuration (created in __construct()) to
     * work.
     */
    public function init()
    {
        $this->_set_environment_config();
        $this->_set_config();
        $this->_load_files();
        $this->_dispatcher = new Rails_ActionDispatch();
        $this->_dispatcher->init();
        $this->_load_activerecord();
        $this->_load_default_models();
        $this->_init();
    }
    
    public function initialize()
    {
        $this->_dispatcher->load_request_and_params();
        $this->_dispatcher->find_route();
        
        if ($this->dispatcher()->router()->route()->rails_admin())
            $this->_set_rails_admin_config();
        
        $this->_load_app_controller();
        $this->_load_controller();
        
        // $this->_load_helpers();
        $this->_load_model();
        
        $this->controller()->run_request_action();
        // $this->_dispatcher->run_action();
        
        $this->_dispatcher->respond();
    }
    
    public function config($idx = null, $subidx = null)
    {
        if ($idx) {
            if ($subidx) {
                if (!isset($this->_config[$idx][$subidx]))
                    Rails::raise('Rails_Application_Error', "Config indexes '[%s][%s]' don't exist.", array($idx, $subidx));
                return $this->_config[$idx][$subidx];
            } else {
                if (!isset($this->_config[$idx]))
                    Rails::raise('Rails_Application_Error', "Config index '%s' doesn't exist.", $idx);
                return $this->_config[$idx];
            }
        } else
            return $this->_config;
    }
    
    public function configure($config)
    {
        $this->_config = $this->_merge_configs($this->_config, $config);
    }
    
    public function dispatcher()
    {
        return $this->_dispatcher;
    }
    
    public function controller()
    {
        return $this->_controller;
    }
    
    public function I18n()
    {
        if (!$this->_I18n) {
            Rails::load_klass('Rails_I18n');
            $this->_I18n = new Rails_I18n();
        }
        return $this->_I18n;
    }
    
    public function name()
    {
        return get_called_class();
    }
    
    /**
     * For custom init.
     */
    protected function _init()
    {
    }
    
    /**
     * Used to set initial configuration in /config/application.php
     * to overwrite default configuration.
     *
     * @see _default_config()
     * @return array.
     */
    protected function _initial_config()
    {
        return array();
    }
    
    private function _load_app_controller()
    {
        $config = Rails::config();
        if (!is_file($app_controller_path = $config['application_controller_filename']))
            throw new Rails_Application_Exception("Couldn't find file for ApplicationController in %s", $app_controller_path);
        
        require $app_controller_path;
        
        if (!class_exists('ApplicationController', false))
            throw new Rails_Application_Exception('Application Controller file %s doesn\'t contain ApplicationController class.', $app_controller_path);
    }
    
    private function _load_controller()
    {
        $controller = $this->dispatcher()->router()->route()->controller;
        
        $ctrlr_name = $controller . '_controller.php';
        
        $ctrlr_filename = Rails::config('controllers_path') . '/' . $ctrlr_name;
        
        if (!is_file($ctrlr_filename))
            Rails::raise('Rails_Application_Exception', "Controller file for '%s' couldn't be found.",
                         $this->dispatcher()->router()->route()->controller);
        
        require $ctrlr_filename;
        
        $class_name = Rails::lower_to_camel($controller) . 'Controller';
        
        if (!class_exists($class_name, false))
            Rails::raise('Rails_Application_Exception', 'Controller file doesn\'t contain expected class (' . $class_name .')');
        
        $controller = new $class_name();
        
        if (!$controller instanceof ActionController_Base)
            Rails::raise('Rails_Application_Exception', 'Controller \'' . $class_name . '\' must be child of ActionController_Base.');
        
        $this->_controller = $controller;
    }
    
    /**
     * Load default files.
     */
    private function _load_files()
    {
        $config = $this->config('app');
        foreach ($config['load_files'] as $file)
            require RAILS_ROOT . $file;
    }
    
    # TODO: this could be somewhere else.
    private function _load_default_models()
    {
        foreach ($this->config('activerecord', 'load_models') as $model)
            ActiveRecord::load_model($model);
    }
    
    private function _load_activerecord()
    {
        // Rails::load_klass('ActiveRecord');
        $config = $this->config();
        
        if ($config['activerecord']['connection'])
            ActiveRecord::add_connection(array(
                                         'connection'     => $config['activerecord']['connection'],
                                         'username'       => $config['activerecord']['username'],
                                         'password'       => $config['activerecord']['password'],
                                         'driver_options' => $config['activerecord']['driver_options'],
                                         'pdo_attributes' => $config['activerecord']['pdo_attributes']),
                                         'default'
            );
    }
    
    private function _load_model()
    {
        $name = Rails::lower_to_camel($this->_dispatcher->router()->route()->controller);
        if (!class_exists($name, false))
            ActiveRecord::load_model($name, false);
    }

    private function _set_rails_admin_config()
    {
        $config = array();
        
        $file = '/controllers/application_controller.php';
        $config['application_controller_filename'] = Rails::config('rails_admin_files') . $file;
        
        $file = '/controllers/admin_controller.php';
        if (is_file($ctrlr_filename = Rails::config('rails_admin_custom') . $file))
            $config['controllers_path'] = Rails::config('rails_admin_custom') . '/controllers';
        else
            $config['controllers_path'] = Rails::config('rails_admin_files') . '/controllers';
        
        if (!is_file($trait = Rails::config('rails_admin_custom') . '/traits/admin_controller.php'))
            $trait = Rails::config('rails_admin_files') . '/traits/admin_controller.php';
        require $trait;
        
        if (!is_file($trait = Rails::config('rails_admin_custom') . '/traits/application_controller.php'))
            $trait = Rails::config('rails_admin_files') . '/traits/application_controller.php';
        require $trait;
        
        $config['app_helper_filename'] = Rails::config('rails_admin_files') . '/helpers/application_helper.php';
        
        // $custom_views_path =
        $config['views_path'] = Rails::config('rails_admin_files') . '/views';
        
        $config['layouts_path'] = Rails::config('rails_admin_files') . '/views/layouts';
        
        $custom_helpers_path = Rails::config('rails_admin_custom') . '/helpers';
        $config['helpers_path'] = is_dir($custom_helpers_path) ? $custom_helpers_path : Rails::config('rails_admin_files') . '/helpers';
        
        // $config['application_controller_filename'] = 
        
        Rails::configure($config);
    }
    
    private function _set_initial_config()
    {
        $default_config = $this->_default_config();
        $initial_config = $this->_initial_config();
        $this->_config = $this->_merge_configs($default_config, $initial_config);
    }
    
    private function _set_environment_config()
    {
        $rails_config = Rails::config();
        $config = $this->config();
        require $rails_config['environments_path'] . '/' . $config['app']['environment'] . '.php';
    }
    
    /**
     * Sets some config like action_on_error.
     */
    private function _set_config()
    {
        if ($this->_config['app']['log_errors']) {
            if (function_exists('ini_set')) {
                ini_set('log_errors', true);
                ini_set('error_log', $this->_config['app']['error_log']);
            }
        }
        
        set_error_handler('Rails::error_handler');
        
        if ($session_name = $this->config('app', 'session')) {
            if (is_string($session_name))
                session_name($session_name);
            session_start();
        }
    }
    
    private function _default_config()
    {
        return [
            'app' => [
                'encoding'      => 'utf-8',
                'load_files'    => [],
                'plugins'       => [],
                'environment'   => 'development',
                
                'error_reporting' => E_ALL,
                'log_errors'      => true,
                'log_php_errors'  => true, // Set to true if PHP will Not log errors itself.
                'error_log'       => Rails::config('logs_path') . '/error.log', // This value shouldn't be edited actually.
                'log_errors_max_len' => 1024,
                'action_on_error' => $_SERVER['REMOTE_ADDR'] === '127.0.0.1' ? self::CONFIG_ERROR_SHOW_STACKTRACE : self::CONFIG_ERROR_SHOW_500_PAGE,
                
                'date_timezone' => 'Europe/Berlin',
                'base_url'      => '', // must NOT include trailing slash.
                
                'rails_admin_url' => 'railsadmin', // NO leading nor trailing slashes
                'rails_admin_ips' => [
                    '127.0.0.1'
                ],
                
                'session' => $this->name()
            ],
            
            'i18n' => [
                'path'    => RAILS_ROOT . '/config/locales',
                'default' => 'en',
                'ext'     => 'php'
            ],
            
            'activerecord' => [
                'connection'        => null,
                'username'          => null,
                'password'          => null,
                'driver_options'    => [],
                'pdo_attributes'    => [
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ],
                'load_models'       => [],
                'action_on_error'   => self::CONFIG_ERROR_SHOW_STACKTRACE,
                'table_schema_from_files' => false
            ],
            
            'actionview' => [
                'layout' => 'application'
            ],
            
            'cookies' => [
                'expire'   => 0,
                'path'     => '/',
                'domain'   => null,
                'secure'   => false,
                'httponly' => false
            ]
        ];
    }
    
    private function _merge_configs($conf1, $conf2)
    {
        $config = array();
        foreach ($conf1 as $name => $value) {
            if (isset($conf2[$name]))
                $config[$name] = array_merge($conf1[$name], $conf2[$name]);
            else
                $config[$name] = $conf1[$name];
        }
        return $config;
    }
}