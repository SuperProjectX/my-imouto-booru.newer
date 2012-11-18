<?php
class ActiveRecord_Table extends ActiveRecord
{
    private $_indexes;
    
    private $_columns;

    public function __construct($table_name)
    {
        $config = Rails::application()->config('activerecord');
        $rconf  = Rails::config('table_schema_path');
        
        if ($config['table_schema_from_files'])
            require $rconf . '/' . $table_name . '.php';
        else {
            $stmt = self::execute_sql("DESCRIBE ".$table_name);
            if (!$data = $stmt->fetchAll()) {
                self::raise('ActiveRecord_Table_Exception', 'Couldn\'t find schema for '.$table_name.'.', $stmt);
            }
            
            $table_data = $table_indexes = $pri = $uni = array();
            
            foreach ($data as $d) {
                $table_data[$d['Field']] = array(
                    'type'    => $d['Type']
                );
            }
            
            $stmt = self::execute_sql("SHOW INDEX FROM ".$table_name);
            $idxs = $stmt->fetchAll();
            
            if ($idxs) {
                foreach ($idxs as $idx) {
                    if ($idx['Key_name'] == 'PRIMARY') {
                        $pri[] = $idx['Column_name'];
                    } elseif ($idx['Non_unique'] === '0') {
                        $uni[] = $idx['Column_name'];
                    }
                }
            }
            
            if ($pri)
                $table_indexes['PRI'] = $pri;
            elseif ($uni)
                $table_indexes['UNI'] = $uni;
            
            $this->_columns = $table_data;
            $this->_indexes = $table_indexes;
        }
    }
    
    public function column_names()
    {
        return array_keys($this->_columns);
    }
    
    public function primary_key()
    {
        $keys = $this->indexes('pri');
        if ($keys)
            return $keys[0];
    }
    
    public function indexes($type = null)
    {
        if (!$type) {
            return $this->_indexes ? current($this->_indexes) : array();
        } else {
            if (isset($this->_indexes[strtoupper($type)]))
                return $this->_indexes[strtoupper($type)];
        }
        return false;
    }
    
    public function column_type($column_name)
    {
        return $this->_columns[$column_name]['type'];
    }
    
    public function column_exists($column_name)
    {
        return !empty($this->_columns[$column_name]);
    }
}