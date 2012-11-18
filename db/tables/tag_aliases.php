<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11)',
  ),
  'name' => 
  array (
    'type' => 'varchar(64)',
  ),
  'alias_id' => 
  array (
    'type' => 'int(11)',
  ),
  'is_pending' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'reason' => 
  array (
    'type' => 'varchar(128)',
  ),
  'creator_id' => 
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