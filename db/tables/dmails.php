<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11)',
  ),
  'from_id' => 
  array (
    'type' => 'int(11)',
  ),
  'to_id' => 
  array (
    'type' => 'int(11)',
  ),
  'title' => 
  array (
    'type' => 'text',
  ),
  'body' => 
  array (
    'type' => 'text',
  ),
  'created_at' => 
  array (
    'type' => 'datetime',
  ),
  'has_seen' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'parent_id' => 
  array (
    'type' => 'int(11)',
  ),
);
$this->_indexes = array (
  'PRI' => 
  array (
    0 => 'id',
  ),
);