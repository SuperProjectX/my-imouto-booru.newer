<?php
abstract class ActiveRecord
{
    private static $_connections = array();
    
    private static $_active_connection;
    
    /**
     * If an error ocurred when calling execute_sql(),
     * it will be stored here.
     */
    private static $_last_error;
    
    public static function add_connection($data, $name)
    {
        if (is_array($data)) {
            $data = array_merge(
                array(
                    'connection'     => null,
                    'username'       => null,
                    'password'       => null,
                    'driver_options' => array(),
                    'pdo_attributes' => array()
                ),
                $data
            );
            
            $pdo = self::_create_connection($data);
        } elseif ($data instanceof PDO) {
            $pdo = $data;
        } else {
            Rails::raise('InvalidArgumentException', 'Connection must be either array or instance of PDO.');
        }
        
        self::$_connections[$name] = $pdo;
        
        if (count(self::$_connections) == 1)
            self::set_connection($name);
    }
    
    public static function set_connection($name)
    {
        if (!isset(self::$_connections[$name]))
            throw new ActiveRecord_Exception('Connection ' . $name . ' does not exist');
        self::$_active_connection = $name;
    }
    
    public static function connection()
    {
        if (!self::$_active_connection)
            Rails::raise('ActiveRecord_Exception', "No connection is active.");
        return self::$_connections[self::$_active_connection];
    }
    
    /**
     * Executes the sql and returns the statement.
     */
    public static function execute_sql()
    {
        $params = func_get_args();
        $sql = array_shift($params);
        
        if (!$sql)
            Rails::raise("ActiveRecord_Exception", "Can't execute SQL without SQL.");
        elseif (is_array($sql)) {
            $params = $sql;
            $sql = array_shift($params);
        }
        
        self::_parse_query_multimark($sql, $params);
        $stmt = self::connection()->prepare($sql);
        
        if (!$stmt->execute($params) && Rails::application()->config('app', 'environment') == 'development') {
            list($code, $drvrcode, $msg) = $stmt->errorInfo();
            self::raise('ActiveRecord_Exception', "[PDOStatement error] [SQLSTATE %s] (%s) %s", array($code, $drvrcode, $msg), $stmt, $params);
        }
        // ob_end_clean();
        $err = $stmt->errorInfo();
        self::$_last_error = $err[2] ? $err : false;
        // if (self::$_last_error && Rails::application()->config('app', 'environment') == 'development')
            // Rails::raise('ActiveRecord_Exception', "SQL error: %s", self::$_last_error);
        return $stmt;
    }
    
    /**
     * Executes the sql and returns the results.
     */
    public static function query()
    {
        // $stmt = self::execute_sql($sql, $params);
        $stmt = call_user_func_array( __CLASS__ . '::execute_sql', func_get_args());
        if (self::last_error())
            return false;
        return $stmt->fetchAll();
    }
    
    static public function select()
    {
        // $sql = 'SELECT ' . $sql;
        // $stmt = self::execute_sql($sql, $params);
        $stmt = call_user_func_array( __CLASS__ . '::execute_sql', func_get_args());
        if (self::last_error())
            return false;
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    static public function select_row()
    {
        // $sql = 'SELECT ' . $sql;
        // $stmt = self::execute_sql($sql, $params);
        $stmt = call_user_func_array( __CLASS__ . '::execute_sql', func_get_args());
        if (self::last_error())
            return false;
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    static public function select_values()
    {
        // $sql = 'SELECT ' . $sql;
        
        // $stmt = self::execute_sql($sql, $params);
        $stmt = call_user_func_array( __CLASS__ . '::execute_sql', func_get_args());
        if (self::last_error())
            return false;
        
        $cols = array();
        if ($data = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
            foreach ($data as $d)
                $cols[] = current($d);
        }
        return $cols;
    }
    
    static public function select_value()
    {
        // $stmt = self::execute_sql($sql, $params);
        $stmt = call_user_func_array( __CLASS__ . '::execute_sql', func_get_args());
        if (self::last_error())
            return false;
        
        if ($data = $stmt->fetch()) {
            $data = array_shift($data);
        }
        return $data;
    }
    
    static public function last_error()
    {
        return self::$_last_error;
    }
    
    /**
     * $model should be the name of the model (i.e. the class name, e.g. Post, User).
     */
    public static function load_model($model, $raise_exception = true)
    {
        if (is_array($model)) {
            foreach ($model as $m)
                self::_load_model($m, $raise_exception);
        } else {
            self::_load_model($model, $raise_exception);
        }
    }
    
    public static function raise($e_name, $msg, array $params, PDOStatement $stmt, $values = array())
    {
        $params && $msg = call_user_func_array('sprintf', array_merge(array($msg), $params));
        
        Rails::load_klass($e_name);
        
        $e = new $e_name($msg);
        
        if (!$e instanceof ActiveRecord_Exception)
            Rails::raise('ActiveRecord_Exception', "Exception '%s' must be child of ActiveRecord_Exception", $e_name);
        
        $e->set_stmt($stmt);
        $e->set_values($values);
        
        throw $e;
    }
    
    static public function RecordNotFound()
    {
        return 'ActiveRecord_Exception_RecordNotFound';
    }
    
    static private function _load_model($model, $raise_exception = true)
    {
        if (class_exists($model, false))
            return;
        
        $name = Rails::camel_to_lower($model);
        $base_path = Rails::config('models_path');
        $filename = $base_path . '/' . $name . '.php';
        
        if (!is_file($filename)) {
            if ($raise_exception)
                Rails::raise('ActiveRecord_Exception_LoadModelError', "Model file for '%s' not found.", $model);
        } else {
            require $filename;
            if (!class_exists($model, false))
                Rails::raise('ActiveRecord_Exception_LoadModelError',
                            "Model file for '%s' doesn't contain expected class (%s).",
                            array($model, $model));
        }
        
        ActionView::add_helper($name);
    }
    
    static protected function _parse_query_multimark(&$query, array &$params)
    {
        # If the number of tokens isn't equal to parameters, ignore
        # it and return. PDOStatement will trigger a Warning.
        if (is_bool(strpos($query, '?')) || substr_count($query, '?') != count($params))
            return;
        
        $parts = explode('?', $query);
        $parsed_params = array();
        
        foreach ($params as $k => $v) {
            if (is_array($v)) {
                $k++;
                $count = count($v);
                $parts[$k] = ($count > 1 ? ', ' . implode(', ', array_fill(0, $count - 1, '?')) : '') . $parts[$k];
                $parsed_params = array_merge($parsed_params, $v);
            } else {
                $parsed_params[] = $v;
            }
        }
        
        $params = $parsed_params;
        $query = implode('?', $parts);
    }
    
    private static function _create_connection($data)
    {
        $pdo = new PDO($data['connection'], $data['username'], $data['password'], $data['driver_options']);
        if ($data['pdo_attributes']) {
            foreach ($data['pdo_attributes'] as $attr => $val)
                $pdo->setAttribute($attr, $val);
        }
        return $pdo;
    }
}