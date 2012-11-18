<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11)',
  ),
  'predicate_id' => 
  array (
    'type' => 'int(11)',
  ),
  'consequent_id' => 
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