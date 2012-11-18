<?php
class ActsAsVersioned_Exception extends Exception
{}

trait ActsAsVersioned
{
    abstract protected function _acts_as_versioned();
    
    public function revert_to($version)
    {
        extract($this->_version_params());
        
        $primary_key = self::_registry()->table()->primary_key();
        
        $default_params = array('conditions' => array('`'.$foreign_key.'` = ? AND `version` = ?', $this->$primary_key, $version), 'from' => '`'.$table_name.'`');
        $sql_params = array_merge($default_params, $this->_version_params());
        
        $query = self::_build_query($sql_params);
        
        $query->execute();
        
        $restoring = $query->stmt()->fetchAll(PDO::FETCH_ASSOC);
        
        if (!$restoring)
            return false;
        
        $restoring = current($restoring);
        
        unset($restoring[$foreign_key], $restoring[$primary_key]);
        
        $this->add_attributes($restoring);
        
        return true;
    }
    
    protected function _versioning_callbacks()
    {
        return [
            'before_save' => ['_set_version'],
            'after_save'  => ['_version_this']
        ];
    }
    
    protected function _set_version()
    {
        $this->version = $this->_next_version();
    }
    
    protected function _version_this()
    {
        extract($this->_version_params());
        $primary_key = self::_registry()->table()->primary_key();
        
        if (empty($class_name)) {
            if (empty($table_name))
                Rails::raise('ActsAsVersioned_Exception', 'Table name for versioning not declared');
            $class_name = substr(Rails::lower_to_camel($table_name), 0, -1);
        }
        
        $versioned = new $class_name();
        $versioned->add_attributes($this->attributes());
        $versioned->$foreign_key = $this->$primary_key;
        return $versioned->save();
    }
    
    protected function _next_version()
    {
        extract($this->_version_params());
        $primary_key = self::_registry()->table()->primary_key();
        $value = self::select_value('SELECT version FROM `'.$table_name.'` WHERE `'.$foreign_key.'` = ? ORDER BY version DESC', $this->$primary_key) ?: 0;
        return $value + 1;
    }
    
    private function _version_params()
    {
        return array_merge($this->_default_version_params(), $this->_acts_as_versioned());
    }
    
    private function _default_version_params()
    {
        return array(
            'table_name'  => self::_cn() . '_versions',
            'foreign_key' => self::_cn() . '_id'
        );
    }
}