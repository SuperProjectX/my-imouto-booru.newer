<?php
$this->_columns = array (
  'id' => 
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
  'title' => 
  array (
    'type' => 'text',
  ),
  'body' => 
  array (
    'type' => 'text',
  ),
  'creator_id' => 
  array (
    'type' => 'int(11)',
  ),
  'parent_id' => 
  array (
    'type' => 'int(11)',
  ),
  'last_updated_by' => 
  array (
    'type' => 'int(11)',
  ),
  'is_sticky' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'response_count' => 
  array (
    'type' => 'int(11)',
  ),
  'is_locked' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'text_search_index' => 
  array (
    'type' => 'text',
  ),
);
$this->_indexes = array (
  'PRI' => 
  array (
    0 => 'id',
  ),
);