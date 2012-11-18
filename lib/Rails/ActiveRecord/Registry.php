<?php
/**
 * This class is supposed to store all the
 * models created. Since they're objects, this
 * serves as cache. We will save up memory and
 * sql queries. This is supposed to be great.
 *
 * Former ModelData, ApplicationModel
 */
class ActiveRecord_Registry
{
    /**
     * This array's indexes will be the names
     * of the models.
     *
     * It will look something like this:
     *
        'Post' => array(
            'table_name' => 'posts',
            'table'      => ActiveRecord_ModelTable instance,
            'instances'  => array(
                $model_1_id => $model_1,
                $model_2_id => $model_2
            )
        );
     *
     * PriKey will be used for quick find.
     * If we need to find the model by an attribute
     * other than its PriKey... Maybe we could do
     * the query, but then search in the registry
     * for the model, now that we have the PK. If
     * it's there, retrieve the model! else, add it.
     */
    private $_reg = array();
    
    /**
     * Sets model to which methods will respond to.
     */
    private $_current_model;
    
    /**
     * @var string $model_name Model's class name
     */
    public function model($model_name)
    {
        if (!isset($this->_reg[$model_name])) {
            if (!defined($model_name . '::table_name')) {
                $table_name = Rails::camel_to_lower($model_name);
                if (substr($table_name, -1) != 's')
                    $table_name .= 's';
            } else
                $table_name = null;
            
            $this->_reg[$model_name] = array(
                'table_name' => $table_name,
                'table'      => null,
                'instances'  => array()
            );
        }
        $this->_current_model = $model_name;
        return $this;
    }
    
    public function table_name()
    {
        if ($this->_reg[$this->_current_model]['table_name'])
            return $this->_reg[$this->_current_model]['table_name'];
        else {
            $cn = $this->_current_model;
            return $cn::table_name;
        }
    }
    
    public function table()
    {
        if (empty($this->_reg[$this->_current_model]['table'])) {
            Rails::load_klass('ActiveRecord_Table');
            $this->_reg[$this->_current_model]['table'] = new ActiveRecord_Table($this->table_name());
        }
        return $this->_reg[$this->_current_model]['table'];
    }
    
    public function search($id)
    {
        $id = (string)$id;
        if (isset($this->_reg[$this->_current_model]) && isset($this->_reg[$this->_current_model][$id]))
            return $this->_reg[$this->_current_model][$id];
    }
    
    public function register($model)
    {
        if (!$model->id)
            return;
        
        if (!isset($this->_reg[$this->_current_model]))
            $this->_reg[$this->_current_model] = array();
        
        $id = (string)$model->id;
        
        $this->_reg[$this->_current_model][$id] = $model;
        return true;
    }
}