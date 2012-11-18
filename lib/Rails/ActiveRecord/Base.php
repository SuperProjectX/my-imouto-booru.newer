<?php
abstract class ActiveRecord_Base extends ActiveRecord
{
    /**
     * ActiveRecord_Registry instance.
     */
    static private $_registry;
    
    protected $_attributes = array();
    
    /**
     * Modified by _create_model and _create_do
     */
    private $_new_record = true;
    
    /**
     * Holds initial attributes for models
     * without a primary key.
     */
    private $_init_attrs = array();
    
    private $_errors;
    
    private $_changed_attributes = array();
    
    static public function __callStatic($method, $params)
    {
        switch ($method) {
            /**
             * find_name_by_id
             *
             * 'by_' would be used as "conditions" param for SQL creation. If 'by_' is specified in the
             * function name and it's larger than 1, then:
             *  - the values for the conditions can be passed as find_by_user_id_and_name(array($user_id, $name))
             *  - the 'conditions' array in the options must be filled with the values for the conditions,
             *    e.g. find_by_name_and_created_at(array('conditions' => array($name, $created_at)));
             * if it's just one condition, a single value may be passed: find_by_name($name);
             *
             * When doing find_name, a single value will be returned.
             * When doing find_name_and_id, a row will be returned.
             * To return all the rows found, do find_name('all');
             */
            case strpos($method, 'find_') === 0:
                return self::_dynamic_find($method, $params);
                break;
        }
        $cn = self::_cn();
        Rails::raise('ActiveRecord_Base_Exception', "Call to undefined static method %s::%s", array($cn, $method));
    }
    
    static public function create(array $attrs)
    {
        $cn = self::_cn();
        $new_model = new $cn();
        $new_model->add_attributes($attrs);
        $new_model->_create_do();
        return $new_model;
    }
    
    static public function find($params)
    {
        if (is_int($params) || ctype_digit($params) || (is_array($params) && is_indexed($params))) {
            return self::_find_with_ids($params);
        } else {
            $data = false;
            
            # If $params isn't an array, wrong parameters were passed or $params === null.
            # set $data as false so RecordNotFound will be thrown.
            if (is_array($params)) {
                $query = self::_build_query($params);
                
                if ($query->execute()->error())
                    Rails::raise("ActiveRecord_Base_Exception", "Query error: " . $query->error());
                
                $data = $query->stmt()->fetchAll(PDO::FETCH_ASSOC);
            }
            
            if (!$data) {
                $var = str_replace("\n", '', var_export($params, true));
                self::_record_not_found("Couldn't find " . self::_cn() . " with parameters %s.", $var);
            }
            return self::_create_model($data[0]);
        }
    }
    
    static public function find_first(array $params)
    {
        $params['limit'] = 1;
        $query = self::_build_query($params);
        if ($query->execute()->error())
            return false;
        if ($data = $query->stmt()->fetch(PDO::FETCH_ASSOC))
            return self::_create_model($data);
        else
            return null;
    }
    
    static public function find_all($sql_params = array())
    {
        $query = self::_build_query($sql_params);
        if ($err = $query->execute()->error())
            Rails::raise('ActiveRecord_Base_Exception', 'SQL query error: %s', $err);
        
        $extra_params = self::_extract_collection_params_from_query($query);
        // if (get_called_class() == 'Post')
            // vde($sql_params);
        $coll = self::_create_collection($query->stmt()->fetchAll(PDO::FETCH_ASSOC), $extra_params);
        
        return $coll;
    }
    
    static public function paginate($sql_params = [])
    {
        return self::find_all($sql_params);
    }
    
    /**
     * When calling this method for pagination, how do we tell it
     * the values for page and per_page? That's what extra_params is for...
     * Although maybe it's not the most elegant solution.
     * extra_params accepts 'page' and 'per_page', they will be sent to Query
     * where they will be parsed.
     */
    static public function find_by_sql($sql, array $params = array(), array $extra_params = array())
    {
        Rails::load_klass('ActiveRecord_Query');
        
        $query = new ActiveRecord_Query($sql, $params, $extra_params);
        if ($query->execute()->error())
            return false;
        $extra_params = self::_extract_collection_params_from_query($query);
        $data = $query->stmt()->fetchAll(PDO::FETCH_ASSOC);
        
        // $stmt = self::execute_sql($sql);
        // if (self::last_error())
            // return false;
        // $data = $stmt->fetchAll();
        // $extra_params = self::_extract_collection_params_from_sql($sql);
        return self::_create_collection($data, $extra_params);
    }
    
    static public function destroy_all(array $conds)
    {
        foreach (self::find_all(array('conditions' => $conds)) as $m)
            $m->destroy();
    }
    
    static public function update($id, array $attrs)
    {
        $attrs_str = [];
        foreach (array_keys($attrs) as $attr)
            $attrs_str[] = '`'.$attr.'` = ?';
        $sql = "UPDATE `".self::_t()."` SET ".implode(', ', $attrs_str)." WHERE id = ?";
        array_unshift($attrs, $sql);
        $attrs[] = $id;
        self::execute_sql($attrs);
    }
    
    # TODO: Fix this and count()
    static public function exists(array $params)
    {
        $params['conditions'] = $params;
        return self::count($params);
    }
    
    static public function count(array $params)
    {
        $params['select'] = 'COUNT(*)';
        $query = self::_build_query($params);
        if ($query->execute()->error())
            return false;
        if ($data = $query->stmt()->fetch(PDO::FETCH_ASSOC))
            return (int)$data['COUNT(*)'];
        else
            return false;
    }
    
    static public function count_by_sql()
    {
        $stmt = call_user_func_array('ActiveRecord::execute_sql', func_get_args());
        if (self::last_error())
            return false;
        $rows = $stmt->fetchAll();
        return count($rows);
    }
    
    static public function maximum($attr)
    {
        return self::select_value('SELECT MAX(' . $attr . ') FROM ' . self::_t());
    }
    
    static public function RecordNotFound($message = '', $code = 0, Exception $previous = null)
    {
        return new ActiveRecord_Exception_RecordNotFound($message, $code, $previous);
    }
    
    static protected function _cn($lower = false)
    {
        $cn = get_called_class();
        return $lower ? strtolower($cn) : $cn;
    }
    
    static protected function _t()
    {
        return self::_registry()->table_name();
    }
    
    /**
     * Shortcut to raise RecordNotFound.
     * Yes. I'm lazy.
     */
    static protected function _record_not_found($str, $params = null)
    {
        Rails::raise('ActiveRecord_Exception_RecordNotFound', $str, $params, array('status' => 404));
    }
    
    # Former create_sql()
    static protected function _build_query(array $params)
    {
        Rails::load_klass('ActiveRecord_Query');
        !isset($params['from']) && $params['from'] = '`' . self::_t() . '`';
        return new ActiveRecord_Query($params);
    }
    
    static protected function _registry()
    {
        if (!self::$_registry) {
            Rails::load_klass('ActiveRecord_Registry');
            self::$_registry = new ActiveRecord_Registry();
        }
        return self::$_registry->model(self::_cn());
    }
    
    /**
     * Creates and returns a non-empty model.
     * This function is useful to centralize the creation of non-empty models,
     * since _new_record must set to null by passing non_empty.
     */
    static private function _create_model(array $data)
    {
        $cn = self::_cn();
        $model = new $cn($data);
        
        # Check for indexes and set init_attrs.
        if (!self::_registry()->table()->indexes()) {
            $model->_init_attrs = $data;
        }
        
        $model->_init();
        $model->_new_record = false;
        $model->_register();
        return $model;
    }
    
    static private function _create_collection(array $data, $query = null)
    {
        static $l;
        if (!$l) {
            Rails::load_klass('ActiveRecord_Collection');
            $l = true;
        }
        $models = array();
        $cn = self::_cn();
        foreach ($data as $d)
            $models[] = self::_create_model($d);
        
        $coll = new ActiveRecord_Collection($models, $query);
        
        return $coll;
    }
    
    static private function _extract_collection_params_from_query(ActiveRecord_Query $query)
    {
        $extra_params = array(
            'page'     => $query->page(),
            'per_page' => $query->per_page(),
            'offset'   => $query->offset(),
            'rows'     => $query->found_rows()
        );
        return $extra_params;
    }
    
    static private function _find_with_ids($ids)
    {
        $return_array = true;
        
        if (!is_array($ids)) {
            $ids = array($ids);
            $return_array = false;
        }

        $ids = array_filter(array_unique($ids));

        $length = count($ids);

        $primary_key = self::_registry()->table()->primary_key();

        if (!$length) {
            self::_record_not_found("Couldn't find ".self::_cn()." without an ID.");
        } elseif ($length == 1) {
            $query = self::_build_query(array('conditions' => array('`'.self::_t().'`.'.$primary_key.' = ? ', $ids[0])));
            $query->execute();
            
            if (!$data = $query->stmt()->fetchAll(PDO::FETCH_ASSOC)) {
                self::_record_not_found("Couldn't find ".self::_cn()." with ".$primary_key."=".current($ids));
            }
            
            $model = self::_create_model(current($data));
            return $model;

        } else {
            $query = self::_build_query(array('conditions' => array('`'.self::_t().'`.`'.$primary_key.'` IN (?)', $ids)));
            if ($query->execute()->error())
                Rais::raise("ActiveRecord_Base_Exception", "Query error: " . $sql->error());
            
            $data = $query->stmt()->fetchAll(PDO::FETCH_ASSOC);
            
            if (count($data) !== count($ids)) {
                $error = 'Couldn\'t find all ' . self::_cn() . ' with IDs (' . implode(',', $ids) . ') (found '.count($data).' but was looking for '.count($ids).')';
                self::_record_not_found($error);
            }

            $models = self::_create_collection($data);

            return $models;
        }
    }
    
    static private function _dynamic_find($method, $params)
    {
        Rails::load_klass('ActiveRecord_DynamicFind');
        $find = new ActiveRecord_DynamicFind($method, $params, self::_t(), self::_cn());
        return $find->parse();
    }
    
    public function __construct(array $attrs = array())
    {
        $this->_attributes = $attrs;
    }
    
    public function __get($prop)
    {
        if (isset($this->_attributes[$prop]))
            return $this->_attributes[$prop];
        elseif ($assoc = $this->_association_exists($prop)) {
            $this->_load_association($prop, $assoc[0], $assoc[1]);
            return $this->_attributes[$prop];
        }
        // else
            // Rails::raise('ActiveRecord_Base_Exception', 'Undefined property ' . self::_cn() . '::$' . $prop . '.');
    }
    
    public function __set($prop, $val)
    {
        $this->_attributes[$prop] = $val;
    }
    
    public function __isset($prop)
    {
        return isset($this->_attributes[$prop]);
    }
    
    public function __call($method, $params)
    {
        switch (true) {
            # To check if an attribute has changed upon save();
            case (is_int(strpos($method, '_changed')) && (strlen($method) - strpos($method, '_changed')) == 8 && strpos($method, '_') === 0) :
                $attribute = substr(str_replace('_changed', '', $method), 1);
                return array_key_exists($attribute, $this->changed_attributes());
                break;
            
            # To check what an attribute was before save() or update_attributes();
            case ((strlen($method) - strpos($method, '_was')) == 4 && strpos($method, '_') === 0) :
                $attribute = substr(str_replace('_was', '', $method), 1);
                return $this->_model_attribute_was($attribute);
                break;
            # Calling static methods from non-static context will trigger
            # __call instead of __callStatic.
            # Support for dynamic find must be added here as well.
            case strpos($method, 'find_') === 0:
                return self::_dynamic_find($method, $params);
                break;
        }
    
        $cn = self::_cn();
        Rails::raise('ActiveRecord_Base_Exception', "Call to undefined method %s::%s()", array($cn, $method));
    }
    
    public function new_record()
    {
        return $this->_new_record;
    }
    
    /**
     * Add/change attributes to model
     *
     * Filters protected attributes of the model.
     * Also calls the "on_attribute_change()" method, if exists, of the model,
     * in case extra operation is needed when changing certain attribute.
     * It's intended to be an equivalent to "def attribute=(val)" in rails.
     * E.g. "is_held" for post model.
     */
    public function add_attributes(array $attrs, array $opts = array())
    {
        $this->_filter_protected_attributes($attrs);
        
        foreach ($attrs as $attr => $v) {
            $on_change_method = '_on_' . $attr . '_change';
            
            if (empty($opts['ignore_on_change']) && (!isset($this->_attributes[$attr]) || $this->$attr != $v) && method_exists($this, $on_change_method)) {
                /**
                 * If on_change_method returns something that's not null, the result will be taken as the
                 * attribute value. Otherwise, it is assumed the function did set the value for the attribute.
                 */
                if (($val = $this->$on_change_method($v)) !== null)
                    $this->$attr = $val;
            } else {
                $this->$attr = $v;
            }
        }
    }
    
    public function attr_exists($attr)
    {
        return array_key_exists($attr, $this->_attributes);
    }
    
    public function attribute($name, $val = null)
    {
        if (func_num_args() > 1) {
            $this->_attributes[$name] = $val;
            return $this;
        } else {
            return $this->_attributes[$name];
        }
    }
    
    public function attributes()
    {
        return $this->_attributes;
    }
    
    public function update_attributes(array $attrs)
    {
        // vd($this);
        $this->add_attributes($attrs);
        // vde($this);
        $this->_run_callbacks('before_update');
        
        if ($this->save()) {
            $this->_run_callbacks('after_update');
            return true;
        }
        return false;
    }
    
    public function update_attribute($attr, $value)
    {
        $this->_attributes[$attr] = $value;
        return $this->save(['skip_validation' => true, 'skip_callbacks' => true]);
    }
    
    /**
     * Save object
     *
     * Saves in the database the properties of the object that match
     * the columns of the corresponding table in the database.
     *
     * Is the model is new, create() will be called instead.
     *
     * @array $values: If present, object will be updated according
     * to this array, otherwise, according to its properties.
     */
    public function save(array $opts = array())
    {
        if (empty($opts['skip_validation'])) {
            if (!$this->_validate_data('save'))
                return false;
        }
        
        if ($this->new_record()) {
            if (!$this->_create_do())
                return false;
        } else {
            if (empty($opts['skip_callbacks'])) {
                if (!$this->_run_callbacks('before_save'))
                    return false;
            }
            if (!$this->_save_do())
                return false;
            if (empty($opts['skip_callbacks'])) {
                $this->_run_callbacks('after_save');
            }
        }
        return true;
    }
    
    /**
     * Delete
     *
     * Deletes row from database based on Primary keys.
     * Properties are also deleted.
     */
    public function delete()
    {
        if ($this->_delete_from_db('delete')) {
            foreach (array_keys(get_object_vars($this)) as $p)
                unset($this->$p);
            return true;
        }
        return false;
    }
    
    # Deletes current model from database but keeps its properties.
    public function destroy()
    {
        return $this->_delete_from_db('destroy');
    }
    
    public function errors()
    {
        if (!$this->_errors) {
            Rails::load_klass('ActiveRecord_Base_Errors');
            $this->_errors = new ActiveRecord_Base_Errors();
        }
        return $this->_errors;
    }
    
    public function reload()
    {
        try {
            $data = $this->_get_stored_data();
        } catch (ActiveRecord_Base_Exception $e) {
            return false;
        }
        
        $arr = (array)$this;
        $i = 0;
        $permanent_attrs = count(get_class_vars(self::_cn()));
        foreach (array_keys($arr) as $attr) {
            $i++;
            if ($i < $permanent_attrs + 1)
                continue;
            unset($this->$attr);
        }
        
        $this->_attributes = $data->attributes();
        $this->_init();
        
        return true;
    }
    
    public function changed_attributes()
    {
        return $this->_changed_attributes;
    }
    
    public function attribute_changed($attr)
    {
        return isset($this->_changed_attributes[$attr]);
    }
    
    public function as_json()
    {
        return $this->attributes();
    }
    
    public function to_json()
    {
        return json_encode($this->as_json());
    }
    
    # TODO:
    public function to_xml(array $params = [])
    {
        if (!isset($params['attributes'])) {
            $this->_merge_model_attributes();
            $attrs = $this->attributes();
        } else {
            $attrs = $params['attributes'];
        }
        
        !isset($params['root']) && $params['root'] = str_replace('_', '-', self::_cn());
        
        if (!isset($params['builder'])) {
            $xml = new Rails_Xml($attrs, $params);
            return $xml->as_xml();
        } else {
            $builder = $params['builder'];
            unset($params['builder']);
            $builder->build($attrs, $params);
        }
    }
    
    /**
     * ***************************
     * Default protected methods {
     * ***************************
     */
    
    /**
     * Called by _create_model() and _create_do().
     */
    protected function _init()
    {
    }
    
    protected function _associations()
    {
        return array();
    }
    
    protected function _model_attributes()
    {
    }
    
    /**
     * Example:
     *
     * return array(
     *    'attribute_name' => array(
     *       'property' => rules...
     *    ),
     *    function() {
     *        ...
     *        return true | false;
     *    },
     *    array(
     *        'method_to_call_1',
     *        '_method_to_call_2' => array('on' => 'action_1', 'action_2', ...),
     *        ...
     *    )
     * );
     *
     * In the case of passing a Closure or methods, the error for the attribute,
     * if the validation failed, must be set manually. They aren't supposed to
     * return anything.
     */
    protected function _validations()
    {
        return array();
    }
    
    protected function _callbacks()
    {
        return array();
    }
    
    /**
     * List of the attributes that can't be changed in the model through
     * add_attributes().
     * If both attr_accessible() and attr_protected() are present in the model,
     * only attr_accessible() will be used.
     */
    protected function _attr_protected()
    {
        return array();
    }
    
    /**
     * List of the only attributes that can be changed in the model through
     * add_attributes().
     * If both attr_accessible() and attr_protected() are present in the model,
     * only attr_accessible() will be used.
     */
    protected function _attr_accessible()
    {
        return array();
    }
    /* } */
    
    protected function _set_changed_attribute($attr, $old_value)
    {
        $this->_changed_attributes[$attr] = $old_value;
    }
    
    /**
     * Returns model's data from the database or using _init_attrs.
     */
    protected function _get_stored_data()
    {
        Rails::load_klass('ActiveRecord_Base_Exception');
        
        if (!$indexes = self::_registry()->table()->indexes()) {
            if (!$this->_init_attrs) {
                throw new ActiveRecord_Base_Exception();
            }
        }
        
        $conds_params = $conds = array();
        if ($indexes) {
            foreach ($indexes as $idx) {
                $conds[] = '`'.$idx.'` = ?';
                $conds_params[] = $this->$idx;
            }
        } else {
            $cn = self::_cn();
            $model = new $cn();
            $cols_names = self::_registry()->table()->column_names();
            foreach ($this->_init_attrs as $name => $value) {
                if (in_array($name, $cols_names)) {
                    $model->_attributes[$name] = $value;
                }
            }
            if (!$model->_attributes)
                throw new ActiveRecord_Base_Exception();
            else
                return $model;
        }
        
        $conds = implode(' AND ', $conds);
        array_unshift($conds_params, $conds);
        
        $current = self::find(array('conditions' => $conds_params));
        
        if (!$current)
            throw new ActiveRecord_Base_Exception();
        else
            return $current;
    }
    
    protected function _load_association($prop, $type, array $params)
    {
        $this->{'_find_' . $type}($prop, $params);
        return $this->attribute($prop);
    }
    
    protected function _run_callbacks($callback_name)
    {
        $callbacks = array();
        
        $tmp = $this->_callbacks();
        if (isset($tmp[$callback_name]))
            $callbacks = $tmp[$callback_name];
        
        $callbacks = array_unique(array_filter(array_merge($callbacks, $this->_get_parents_callbacks($callback_name))));
        
        if ($callbacks) {
            foreach ($callbacks as $method) {
                if (false === $this->$method())
                    return false;
            }
        }
        
        return true;
    }
    
    private function _get_parents_callbacks($callback_name)
    {
        $all_callbacks = array();
        if (($class = get_parent_class($this)) != 'ActiveRecord_Base') {
          $class = self::_cn();
          $obj   = new $class();
          while (($class = get_parent_class($obj)) != 'ActiveRecord_Base') {
            $obj = new $class();
            if ($callbacks = $obj->_callbacks()) {
              if (isset($callbacks[$callback_name]))
                $all_callbacks = array_merge($callbacks, $callbacks[$callback_name]); 
            }
          }
        }
        return $all_callbacks;
    }
    
    private function _filter_protected_attributes(&$attributes)
    {
        # Default protected attributes
        $default_protected = array_fill_keys(array_merge(['id', 'created_at', 'updated_at'], $this->_associations_names()), true);
        $attributes = array_diff_key($attributes, $default_protected);
        
        if ($attrs = $this->_attr_accessible()) {
            $attributes = array_intersect_key($attributes, array_fill_keys($attrs, true));
        } elseif ($attrs = $this->_attr_protected()) {
            $attributes = array_diff_key($attributes, array_fill_keys($attrs, true));
        }
    }
    
    # Returns association property names.
    private function _associations_names()
    {
        $associations = array();
        foreach ($this->_associations() as $assocs) {
            foreach ($assocs as $k => $v) {
                if (is_int($k))
                    $associations[] = $v;
                else
                    $associations[] = $k;
            }
        }
        return $associations;
    }
    
    private function _merge_model_attributes()
    {
        if ($attrs = $this->_model_attributes()) {
            if (true === $attrs)
                $attrs = array_diff_key(get_object_vars($this), get_class_vars(get_class()));
            
            foreach ($attrs as $attr)
                $this->_attributes = $this->$attr;
        }
    }
    
    /**
     * @see _validations()
     */
    private function _validate_data($action)
    {
        if (!$this->_run_callbacks('before_validation'))
            return false;
        
        $validation_success = true;
        
        Rails::load_klass('Rails_Validation');
        Rails::load_klass('ActiveRecord_Validation');
        
        foreach ($this->_validations() as $property => $validations) {
            if (is_int($property)) {
                if ($validations instanceof Closure) {
                    $validations();
                } elseif (is_array($validations)) {
                    foreach ($validations as $method => $filters) {
                        if (!is_int($method)) {
                            if (!is_array($filters)) {
                                Rails::raise("ActiveRecord_Base_Exception",
                                    "Invalid 'on' filter for custom methods for validation, must be an array, %s passed",
                                    gettype($filters));
                            }
                            
                            if (!empty($filter['on']) && !in_array($action, $filter['on']))
                                continue;
                        } else {
                            $method = $filters;
                        }
                        
                        $this->$method();
                        
                        if (!$this->errors()->blank())
                            $validation_success = false;
                    }
                } else {
                    Rails::raise("ActiveRecord_Base_Exception",
                        "Custom validation accepts either a Closure of an array, %s passed",
                        gettype($validations));
                }
            } else {
                foreach ($validations as $type => $params) {
                    if (!is_array($params)) {
                        $rule = $params;
                        $params = array();
                    } else {
                        $rule = array_shift($params);
                    }
                    $validation = new ActiveRecord_Validation($type, $this->$property, $rule, $params);
                    $validation->set_params($action, $this, $property);
                    
                    if (!$validation->validate()->success()) {
                        $validation->set_error_message();
                        $validation_success = false;
                    }
                }
            }
        }
        return $validation_success;
    }
    
    private function _create_do()
    {
        if (!$this->_run_callbacks('before_validation_on_create')) {
            return false;
        } elseif (!$this->_validate_data('create'))
            return false;
        
        $this->_run_callbacks('after_validation_on_create');
        
        if (!$this->_run_callbacks('before_save'))
            return false;
        
        if (!$this->_run_callbacks('before_create'))
            return false;
        
        $this->_check_time_column('created_at');
        $this->_check_time_column('updated_at');
        
        $cols_values = $cols_names = array();
        
        $this->_merge_model_attributes();
        
        foreach ($this->attributes() as $attr => $val) {
            if (!$this->_column_exists($attr))
                continue;
            $cols_names[] = '`'.$attr.'`';
            $cols_values[] = $val;
        }
        
        if (!$cols_values)
            return false;
        
        $binding_marks = implode(', ', array_fill(0, (count($cols_names)), '?'));
        $cols_names = implode(', ', $cols_names);
        
        $sql = 'INSERT INTO `'.self::_t().'` ('.$cols_names.') VALUES ('.$binding_marks.')';
        
        array_unshift($cols_values, $sql);
        
        self::execute_sql($cols_values);
        
        $id = self::connection()->lastInsertId();
        
        // array_unshift($values, $sql);
        
        // $id = call_user_func_array('DB::insert', $values);
        
        $primary_key = self::_registry()->table()->indexes('PRI');
        
        if ($primary_key && count($primary_key) == 1) {
            if (!$id) {
                $this->errors()->add_to_base('Couldn\'t retrieve new primary key.');
                return false;
            }
            
            if ($pri_key = self::_registry()->table()->primary_key()) { //$this->get_model_keydata()) {
                // $keycol = key($keycol);
                $this->attribute($pri_key, $id);
            }
        }
        
        $this->_new_record = false;
        $this->_init();
        
        $this->_run_callbacks('after_create');
        $this->_run_callbacks('after_save');
        
        return true;
    }
    
    private function _save_do()
    {
        $w = $wd = $q = $d = array();
        
        $dt = self::_registry()->table()->column_names();
        
        try {
            $current = $this->_get_stored_data();
        } catch (ActiveRecord_Base_Exception $e) {
            $this->errors()->add_to_base($e->getMessage());
            return;
        }
        
        $has_primary_keys = false;
        
        foreach (self::_registry()->table()->indexes() as $idx) {
            $w[] = '`'.$idx.'` = ?';
            $wd[] = $this->$idx;
        }
        
        if ($w)
            $has_primary_keys = true;
        
        $this->_merge_model_attributes();
        
        foreach ($this->attributes() as $prop => $val) {
            # Can't update properties that don't have a column in DB, or
            # PRImary keys, or created_at column.
            if (!in_array($prop, $dt) || $prop == 'created_at' || $prop == 'updated_at') {
                continue;
                
            } elseif (!$has_primary_keys && $val === $current->$prop) {
                $w[] = '`'.$prop.'` = ?';
                $wd[] = $current->$prop;
                
            } elseif ($val != $current->$prop) {
                $this->_set_changed_attribute($prop, $current->$prop);
                $q[] = '`'.$prop.'` = ?';
                $d[] = $val;
            }
        }
        
        # Update `updated_at` if exists.
        if ($this->_check_time_column('updated_at')) {
            $q[] = "`updated_at` = ?";
            $d[] = $this->updated_at;
        }
        
        if ($q) {
            $q = "UPDATE `" . self::_t() . "` SET " . implode(', ', $q);
            $w && $q .= ' WHERE '.implode(' AND ', $w);
            
            $d = array_merge($d, $wd);
            
            array_unshift($d, $q);
            
            self::execute_sql($d);
            
            if (self::last_error()) {
                $this->errors()->add_to_base(self::last_error());
                return false;
            }
        }
        
        $this->_update_init_attrs();
        return true;
    }
    
    private function _delete_from_db($type)
    {
        if (!$keys = self::_registry()->table()->indexes())
            return false;
        
        $w = $wd = array();
        
        foreach ($keys as $k) {
            $w[] = '`'.self::_t().'`.`'.$k.'` = ?';
            $wd[] = $this->$k;
        }
        
        $w = implode(' AND ', $w);
        
        $this->_run_callbacks('before_'.$type);
        
        $query = 'DELETE FROM `'.self::_t().'` WHERE '.$w;
        
        self::execute_sql($query, $wd);
        
        $this->_run_callbacks('after_'.$type);
        
        return true;
    }
    
    private function _model_attribute_was($attr)
    {
        return $this->attribute_changed($attr) ? $this->_changed_attributes[$attr] : null;
    }
    
    private function _association_exists($prop)
    {
        if ($assocs = $this->_associations()) {
            foreach ($assocs as $type => $assoc) {
                foreach ($assoc as $name => $params) {
                    if (is_int($name)) {
                        $name = $params;
                        $params = array();
                    }
                    
                    if ($name == $prop)
                        return array($type, $params);
                }
            }
        }
        return false;
    }
    
    private function _find_has_one($prop, array $params)
    {
        empty($params['class_name']) && $params['class_name'] = Rails::lower_to_camel($prop);
        $find_params = $this->_parse_has($prop, $params);
        $this->attribute($prop, $params['class_name']::find_first($find_params));
    }
    
    private function _find_has_many($prop, array $params)
    {
        empty($params['class_name']) && $params['class_name'] = rtrim(Rails::lower_to_camel($prop), 's');
        $find_params = $this->_parse_has($prop, $params);
        $this->attribute($prop, $params['class_name']::find_all($find_params));
    }
    
    private function _find_belongs_to($prop, array $params)
    {
        empty($params['class_name']) && $params['class_name'] = Rails::lower_to_camel($prop);
        $foreign_key = !empty($params['foreign_key']) ? $params['foreign_key'] : strtolower($params['class_name']).'_id';
        $this->attribute($prop, $this->$foreign_key ? $params['class_name']::find($this->$foreign_key) : false);
    }
    
    private function _parse_has($prop, array $params)
    {
        self::load_model($params['class_name']);
        $params['from'] = $params['class_name']::_t();
        
        if (empty($params['foreign_key']))
            $params['foreign_key'] = substr(self::_t(), 0, -1).'_id';
        
        $conds[] = $params['foreign_key']." = ?";
        $find_params['conditions'] = array($this->id);
        
        if (!empty($params['conditions'])) {
            if (!is_array($params['conditions']))
                $params['conditions'] = [$params['conditions']];
            $conds[] = array_shift($params['conditions']);
            foreach($params['conditions'] as $c)
                $find_params['conditions'][] = $c;
            unset($c);
            unset($params['conditions']);
        }
        
        /**
         * For tables like post that need a table for its tags (posts_tags), it's now only needed
         * to declare a has_many association with params 'assoc_model' => true to create an association.
         * To customize fields, see data below...
         */
        if (!empty($params['assoc_table'])) {
            if (is_bool($params['assoc_table']))
                $params['assoc_table'] = self::_t() . '_' . $params['from'];
            
            if (empty($params['join'])) {
                empty($params['join_type']) && $params['join_type'] = 'JOIN';
                empty($params['assoc_key']) && $params['assoc_key'] = 'id';
                empty($params['assoc_table_key']) && $params['assoc_table_key'] = substr($params['from'], 0, -1) . '_id';
                
                $params['joins'] = $params['join_type'] . ' ' . $params['assoc_table'] . ' ON ' . $params['from'] . '.' . $params['assoc_key'] . ' = ' . $params['assoc_table'] . '.' . $params['assoc_table_key'];
            }
            
            if (empty($params['select'])) {
                $params['select'] = $params['from'] . '.*';
            }
            
            $conds[0] = $params['assoc_table'] . '.' . $params['foreign_key'] . ' = ?';
            
            ## Having Post model with has_many => tags (Tag model):
            # Main parameter
            // 'assoc_table'        => true (=posts_tags, otherwise specify other table name)
            # Optional parameters
            // 'assoc_key'       => id
            // 'foreign_key'     => post_id
            // 'assoc_table_key' => tag_id
            
            # Needed SQL params
            // 'select'     => posts.*
            // 'joins'      => JOIN posts_tags ON posts.id = posts_tags.post_id',
            // 'conditions' => array('posts_tags.post_id = ?', $this->id)
        }
        
        $conds = implode(' AND ', $conds);
        array_unshift($find_params['conditions'], $conds);
        
        $find_params = array_merge($find_params, $params);
        return $find_params;
    }
    
    private function _register()
    {
        self::_registry()->register($this);
    }
    
    private function _column_exists($column)
    {
        return self::_registry()->table()->column_exists($column);
    }
    
    /**
     * Check time column
     *
     * Called by save() and create(), checks if time $column
     * exists and automatically sets a value to it.
     */
    private function _check_time_column($column)
    {
        if (!$this->_column_exists($column))
            return false;
        
        $type = self::_registry()->table()->column_type($column);
        
        if ($type == 'datetime')
            $time = gmd();
        elseif ($type == 'year')
            $time = gmd('Y');
        elseif ($type == 'date')
            $time = gmd('Y-m-d');
        elseif ($type == 'time')
            $time = gmd('H:i:s');
        elseif ($type == 'timestamp')
            $time = time();
        else
            return false;
        
        $this->_attributes[$column] = $time;
        
        return true;
    }
    
    private function _update_init_attrs()
    {
        foreach (array_keys($this->_init_attrs) as $name) {
            if (isset($this->_attributes[$name]))
                $this->_init_attrs[$name] = $this->_attributes[$name];
        }
    }
}