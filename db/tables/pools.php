<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11)',
  ),
  'name' => 
  array (
    'type' => 'varchar(255)',
  ),
  'description' => 
  array (
    'type' => 'varchar(128)',
  ),
  'user_id' => 
  array (
    'type' => 'int(11)',
  ),
  'is_active' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'created_at' => 
  array (
    'type' => 'datetime',
  ),
  'updated_at' => 
  array (
    'type' => 'datetime',
  ),
  'post_count' => 
  array (
    'type' => 'int(3)',
  ),
  'is_public' => 
  array (
    'type' => 'binary(1)',
  ),
);
$this->_indexes = array (
  'PRI' => 
  array (
    0 => 'id',
  ),
);