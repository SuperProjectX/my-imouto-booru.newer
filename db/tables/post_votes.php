<?php
$this->_columns = array (
  'post_id' => 
  array (
    'type' => 'int(11)',
  ),
  'user_id' => 
  array (
    'type' => 'int(11)',
  ),
  'score' => 
  array (
    'type' => 'int(1)',
  ),
  'updated_at' => 
  array (
    'type' => 'datetime',
  ),
);
$this->_indexes = array (
  'UNI' => 
  array (
    0 => 'post_id',
    1 => 'user_id',
  ),
);