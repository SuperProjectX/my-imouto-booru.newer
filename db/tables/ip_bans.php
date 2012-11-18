<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11) unsigned',
  ),
  'created_at' => 
  array (
    'type' => 'datetime',
  ),
  'expires_at' => 
  array (
    'type' => 'datetime',
  ),
  'ip_addr' => 
  array (
    'type' => 'varchar(15)',
  ),
  'reason' => 
  array (
    'type' => 'text',
  ),
  'banned_by' => 
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