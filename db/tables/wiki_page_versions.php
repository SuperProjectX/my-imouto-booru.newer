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
  'version' => 
  array (
    'type' => 'int(11)',
  ),
  'title' => 
  array (
    'type' => 'varchar(64)',
  ),
  'body' => 
  array (
    'type' => 'text',
  ),
  'user_id' => 
  array (
    'type' => 'int(11)',
  ),
  'ip_addr' => 
  array (
    'type' => 'varchar(15)',
  ),
  'wiki_page_id' => 
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