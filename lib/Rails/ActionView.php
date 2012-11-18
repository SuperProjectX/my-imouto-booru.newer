<?php
abstract class ActionView
{
    /**
     * Stores the contents for.
     * Static because contents_for are available
     * for everything.
     */
    private static $_content_for = array();
    
    /**
     * Stores the names of the active content_for's.
     */
    private static $_content_for_names = array();
    
    protected $_buffer;
    
    static private $_helper_queue = array();
    
    static private $_helpers = array();
    
    /**
     * Holds method_name => helper_name for faster
     * calls.
     */
    static private $_helper_registry = array();
    
    /**
     * Adds helper to the helpers queue.
     *
     * @var name string helper name
     * @see _include_helper()
     */
    static public function add_helper($name)
    {
        
        self::$_helper_queue[] = $name;
    }
    
    static public function add_helpers(array $names)
    {
        self::$_helper_queue = array_merge(self::$_helper_queue, $names);
    }
    
    static public function get_helper($name)
    {
        if (isset(self::$_helpers[$name]))
            return self::$_helpers[$name];
        else
            return false;
    }
    
    /**
     * Actually include the helpers files.
     *
     * Application and current controller's helper are added here,
     * to make sure they're top on the list.
     */
    static public function include_helpers()
    {
        array_unshift(self::$_helper_queue, 'application', Rails::application()->dispatcher()->router()->route()->controller);
        foreach (array_unique(self::$_helper_queue) as $name) {
            self::_include_helper($name);
        }
    }
    
    static public function find_helper_for($method)
    {
        if (isset(self::$_helper_registry[$method]))
            return self::$_helpers[self::$_helper_registry[$method]];
        foreach (self::$_helpers as $helper_name => $helper) {
            if (method_exists($helper, $method)) {
                self::$_helper_registry[$method] = $helper_name;
                return $helper;
            }
        }
        return false;
    }
    
    static public function find_helper($helper_name)
    {
        if (!isset(self::$_helpers[$helper_name]))
            throw new ActionView_Exception('Helper \'' . $helper_name . '\' isn\'t loaded.');
        return self::$_helpers[$helper_name];
    }
    
    static public function clean_buffers()
    {
        if ($status = ob_get_status()) {
            foreach (range(0, $status['level']) as $lvl)
                ob_end_clean();
        }
    }
    
    static private function _load_helper_files()
    {
        if (!class_exists('ActionView_Helper', false)) {
            $p = RAILS_ROOT . '/lib/Rails/ActionView/Helper/';
            
            // $traits = glob($p.'*.php');
            
            foreach (glob($p.'*.php') as $t) {
                if (is_int(strpos($t, 'Helper/Exception.php')))
                    continue;
                require $t;
            }
            Rails::load_klass('ActionView_Helper');
        }
    }
    
    /**
     * If the $name value doesn't contain / or \,
     * it's assumed $name is the name of a controller/model,
     * thus the helper will be loaded from /app/helpers.
     * Otherwise, it's taken as a path relative to RAILS_ROOT, and
     * therefore must have a leading slash.
     */
    static private function _include_helper($name)
    {
        $class_count = count(get_declared_classes());
        
        if (!is_object($name)) {
            self::_load_helper_files();
            // Rails::load_klass('ActionView_Helper');
            
            list($filename, $type) = self::_resolve_helper_filename($name);
            
            if (!is_file($filename)) {
                # Silently ignore helpers that don't exist.
                if ($type == 'apphelper')
                    return;
                else
                    Rails::raise('ActionView_Exception', "Helper for '%s' couldn't be found.", $name);
            }
            
            require $filename;
            
            $classes = get_declared_classes();
            
            if ($class_count == count($classes)) {
                $filename = substr($filename, strlen(RAILS_ROOT)+1);
                Rails::raise('ActionView_Exception', "Helper '%s' (%s) doesn't contain any class.", array($name, $filename));
            }
            
            $class_name = array_pop($classes);
            
            $ref = new ReflectionClass($class_name);
            if (!$ref->isInstantiable())
                return;
            
            $helper = new $class_name;
        } else {
            $helper = $name;
            $class_name = $name = get_class($helper);
        }
        
        // if (!$helper instanceof ActionView_Helper)
            // Rails::raise('ActionView_Exception', "Helper '%s' must be child of ActionView_Helper.", $name);
        // else
        if ($type == 'child') {
            if (!$helper_name = $helper->name())
                Rails::raise('ActionView_Exception', "Helper '%s' must define a name.", $name);
            elseif (isset(self::$_helpers[$helper_name]))
                Rails::raise('ActionView_Exception', "Helper '%s' must have a unique name (%s).", $name, $helper_name);
        } else {
            $helper_name = $name . '_helper';
        }
        self::$_helpers[$helper_name] = $helper;
    }
    
    static private function _resolve_helper_filename($name)
    {
        if (is_int(strpos($name, '/')) || is_int(strpos($name, '\\'))) {
            $filename = RAILS_ROOT . $name;
            $type = 'child';
        } else {
            if ($name == 'application') {
                $filename = Rails::config('app_helper_filename');
            } else {
                $helpers_path = Rails::config('helpers_path');
                $filename = $helpers_path . '/' . strtolower($name) . '_helper.php';
            }
            $type = 'apphelper';
        }
        return array($filename, $type);
    }
    
    /**
     * Creates content for $name by calling $block().
     * If no $block is passed, it's checked if content for $name
     * exists.
     */
    public function content_for($name, Closure $block = null, $prefix = false)
    {
        if (!$block)
            return isset(self::$_content_for[$name]);
        
        if (!isset(self::$_content_for[$name]))
            self::$_content_for[$name] = '';
        
        ob_start();
        $block();
        $this->_add_content_for($name, ob_get_clean(), $prefix);
    }
    
    public function provide($name, $content)
    {
        $this->_add_content_for($name, $content, false);
    }
    
    public function clear_content_for($name)
    {
        unset(self::$_content_for[$name]);
    }
    
    public function yield($name = null)
    {
        if ($name && isset(self::$_content_for[$name]))
            return self::$_content_for[$name];
    }
    
    /**
     * Passing a closure to do_content_for() will cause it
     * to do the same as end_content_for(): add the buffered
     * content. Thus, this method.
     *
     * @param string $name content's name
     * @param string $value content's body
     * @param bool $prefix to prefix or not the value to the current value
     */
    private function _add_content_for($name, $value, $prefix)
    {
        !array_key_exists($name, self::$_content_for) && self::$_content_for[$name] = '';
        if ($prefix)
            self::$_content_for[$name] = $value . self::$_content_for[$name];
        else
            self::$_content_for[$name] .= $value;
    }
    // public function ob_start()
    // {
        // ob_start(array($this, 'ob_end'));
    // }
    
    // public function ob_end($buffer)
    // {
        // return $buffer;
        // $this->_buffer = $buffer;
        // return $this->_buffer;
    // }
}