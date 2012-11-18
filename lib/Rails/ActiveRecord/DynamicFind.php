<?php
class ActiveRecord_DynamicFind extends ActiveRecord_Base
{
    private $_method;
    
    private $_params;
    
    private $_data;
    
    private $_t;
    
    private $_cn;
    
    // static protected function _build_query(array $params)
    // {
        // $params['from'] = $from;
        // return parent::_build_query($params);
    // }
    
    public function __construct($method, $params, $t, $cn)
    {
        $this->_method = $method;
        $this->_params = $params;
        $this->_t      = $t;
        $this->_cn     = $cn;
    }
    
    public function parse()
    {
        $method = $this->_method;
        $params = $this->_params;
        
        if (strpos($method, 'find_or_create_by_') === 0)
            return $this->_find_or_create();
        
        $method = substr($method, 4);
        
        if (strpos($method, '_all') === 0) {
            $select_some_rows = true;
            $method = substr($method, 4);
        } else
            $select_some_rows = false;
        
        $by_clause_position = strpos($method, '_by_');
        
        if (is_array(current($params)))
            $params = current($params);
        elseif (count($params) > 1)
            ; # TODO: This should generate an error. If more than 1 value is to be passed, they must be in an array.
        
        # This is done because if _by_ isn't in position 0,
        # then it's a value, and we have to remove the leading underscore.
        if ($by_clause_position !== 0)
            $method = substr($method, 1);
            
        $has_by_caluse = is_int($by_clause_position);
        
        if (!$has_by_caluse) {
            if (is_bool(strpos($method, '_and_'))) {
                if ($select_some_rows) {
                    // find rows
                    $data = $this->_find_some_rows($method, $params);
                } else {
                    // find single value
                    $data = $this->_find_one_value($method, $params);
                }
            } else {
                $values = explode('_and_', $method);
                if ($select_some_rows) {
                    $data = $this->_find_some_rows($values, $params);
                } else {
                    // find one row
                    $data = $this->_find_one_row($values, $params);
                }
            }
        } else {
            list($values, $conditions) = explode('_by_', $method);
            
            # Parsing conditions
            $conditions = implode(' AND ', array_map(function($v){
                return $v . ' = ?';
            }, explode('_and_', $conditions)));
            
            if (!$by_clause_position) {
                !is_array($params) && $params = array($params);
                $sql_params = array(
                    'conditions' => array_merge(array($conditions), $params)
                );
                $cn = $this->_cn;
                if ($select_some_rows)
                    $data = $cn::find_all($sql_params);
                else
                    $data = $cn::find_first($sql_params);
            } else {
                if (is_bool(strpos($values, '_and_'))) {
                 
                    if ($select_some_rows) {
                        $data = $this->_find_some_rows($values, $params, $conditions);
                    } else {
                        // find single value by arguments
                        $data = $this->_find_one_value($values, $params, $conditions);
                    }
                } else {
                    $values = explode('_and_', $values);
                    if ($select_some_rows) {
                        // find some values by arguments
                        $data = $this->_find_some_rows($values, $params, $conditions);
                    } else {
                        $data = $this->_find_one_row($values, $params, $conditions);
                    }
                }
            }
        }
        
        $this->_data = $data;
        return $this->_data;
    }
    
    private function _find_or_create()
    {
        $find_by_method = str_replace('find_or_create', 'find', $this->_method);
        $params = $this->_params;
        $cn = $this->_cn;
        
        if (is_bool(strpos($this->_method, '_and_')))
            $params = array_shift($params);
        
        if (!$model = $cn::$find_by_method($params)) {
            !is_array($params) && $params = array($params);
            $create_params = array();
            
            foreach (explode('_and_', str_replace('find_by_', '', $find_by_method)) as $attr)
                $create_params[$attr] = array_shift($params);
            
            $cn = $this->_cn;
            $model = $cn::create($create_params);
        }
        
        return $model;
    }
    
    /**
     * $conditions will be filled with the function name to take the _by_ part
     * eg: _by_created_at_and_user_id. $args will be taken as the values for 
     * the conditions clause.
     * if $conditions isn't present, $args is one or more ids that will be taken
     * for the conditions.
     */
    private function _find_some_rows(array $values, $args, $conditions = null)
    {
        $sql_params = array('select' => implode(', ', $values), 'conditions' => false);
        
        if (!$conditions) {
            if (count($args) == 1 && (is_int(current($args)) || ctype_digit(current($args)))) {
                # searching, one single value, by id
                $args = array_shift($args);
                $sql_params['conditions'] = array('id = ?', $args);
            } elseif ($this->_is_indexed($args)) {
                # searching by some ids
                $marks = implode(', ', array_map(function($v){return '?';}, $args));
                $sql_params['conditions'] = array('id IN ('.$marks.')', $args);
            } else {
                # arguments are sql parameters. merge with $sql_params.
                $sql_params = array_merge($sql_params, $args);
            }
        } else {
            !is_array($args) && $args = array($args);
            # conditions given. $args must be values for conditions.
            if ($this->_is_indexed($args)) {
                $sql_params['conditions'] = array_merge(array($conditions), $args);
            } else {
                # when using conditions, sql parameters can't be passed
                return false;
            }
        }
        
        $query = self::_build_query(array_merge($sql_params, ['from' => $this->_t]));
        if ($query->execute()->error())
            return false;
        $data = $query->stmt()->fetch(PDO::FETCH_ASSOC);
        
        return $data;
    }
    
    private function _find_one_row(array $value, $args, $conditions = null)
    {
        if ($data = $this->_find_some_rows($value, $args, $conditions))
            $data = array_shift($data);
        
        return $data;
    }
    
    /**
     * $conditions will be filled with the function name to take the _by_ part
     * eg: _by_created_at_and_user_id. $args will be taken as the values for 
     * the conditions clause.
     * if $conditions isn't present, $args is one or more ids that will be taken
     * for the conditions.
     *
     * this function can return either a single value or an array of rows;
     */
    private function _find_one_value($value, $args, $conditions = null)
    {
        $sql_params = array('select' => $value, 'conditions' => false);
        
        if (!$conditions) {
            if (count($args) == 1 && (is_int(current($args)) || ctype_digit(current($args)))) {
                # searching, one single value, by id
                $args = array_shift($args);
                $sql_params['conditions'] = array('id = ?', $args);
            } elseif ($this->_is_indexed($args)) {
                # searching by some ids
                $marks = implode(', ', array_map(function($v){return '?';}, $args));
                $sql_params['conditions'] = array('id IN ('.$marks.')', $args);
            } else {
                # arguments are sql parameters. merge with $sql_params.
                $sql_params = array_merge($sql_params, $args);
            }
            
        } else {
            !is_array($args) && $args = array($args);
            # conditions given. $args must be values for conditions.
            if ($this->_is_indexed($args)) {
                $sql_params['conditions'] = array_merge(array($conditions), $args);
            } else {
                # when using conditions, sql parameters can't be passed
                return false;
            }
        }
        
        $query = self::_build_query(array_merge($sql_params, ['from' => $this->_t]));
        $query->execute();
        
        if ($data = $query->stmt()->fetchAll(PDO::FETCH_ASSOC))
            $data = isset($data[0][$value]) ? $data[0][$value] : false;
        
        return $data;
    }
    
    private function _is_indexed(array $array)
    {
        $i = 0;
        foreach(array_keys($array) as $k) {
            if($k !== $i)
                return false;
            $i++;
        }
        return true;
    }
}