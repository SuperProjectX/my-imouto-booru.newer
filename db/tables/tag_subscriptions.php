<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11)',
  ),
  'user_id' => 
  array (
    'type' => 'int(11)',
  ),
  'tag_query' => 
  array (
    'type' => 'text',
  ),
  'cached_post_ids' => 
  array (
    'type' => 'text',
  ),
  'name' => 
  array (
    'type' => 'varchar(32)',
  ),
  'is_visible_on_profile' => 
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