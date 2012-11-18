<?php
$this->_columns = array (
  'post_id' => 
  array (
    'type' => 'int(11)',
  ),
  'tag_id' => 
  array (
    'type' => 'int(11)',
  ),
);
$this->_indexes = array (
  'UNI' => 
  array (
    0 => 'post_id',
    1 => 'tag_id',
  ),
);