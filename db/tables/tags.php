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
  'post_count' => 
  array (
    'type' => 'int(11)',
  ),
  'cached_related' => 
  array (
    'type' => 'text',
  ),
  'cached_related_expires_on' => 
  array (
    'type' => 'datetime',
  ),
  'tag_type' => 
  array (
    'type' => 'smallint(6)',
  ),
  'is_ambiguous' => 
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