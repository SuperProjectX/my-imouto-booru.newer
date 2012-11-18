<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11)',
  ),
  'post_id' => 
  array (
    'type' => 'int(11)',
  ),
  'user_id' => 
  array (
    'type' => 'int(11)',
  ),
  'ip_addr' => 
  array (
    'type' => 'varchar(16)',
  ),
  'created_at' => 
  array (
    'type' => 'datetime',
  ),
  'body' => 
  array (
    'type' => 'text',
  ),
  'updated_at' => 
  array (
    'type' => 'datetime',
  ),
  'is_spam' => 
  array (
    'type' => 'tinyint(1)',
  ),
);
$this->_indexes = array (
  'PRI' => 
  array (
    0 => 'id',
  ),
);