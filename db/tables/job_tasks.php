<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11)',
  ),
  'task_type' => 
  array (
    'type' => 'varchar(64)',
  ),
  'data_as_json' => 
  array (
    'type' => 'text',
  ),
  'status' => 
  array (
    'type' => 'varchar(64)',
  ),
  'status_message' => 
  array (
    'type' => 'text',
  ),
  'repeat_count' => 
  array (
    'type' => 'int(11)',
  ),
  'created_at' => 
  array (
    'type' => 'datetime',
  ),
  'updated_at' => 
  array (
    'type' => 'datetime',
  ),
);
$this->_indexes = array (
  'PRI' => 
  array (
    0 => 'id',
  ),
);