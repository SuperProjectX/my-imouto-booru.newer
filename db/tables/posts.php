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
  'ip_addr' => 
  array (
    'type' => 'varchar(64)',
  ),
  'file_size' => 
  array (
    'type' => 'int(11)',
  ),
  'md5' => 
  array (
    'type' => 'varchar(32)',
  ),
  'last_commented_at' => 
  array (
    'type' => 'datetime',
  ),
  'file_ext' => 
  array (
    'type' => 'varchar(4)',
  ),
  'last_noted_at' => 
  array (
    'type' => 'datetime',
  ),
  'source' => 
  array (
    'type' => 'varchar(249)',
  ),
  'width' => 
  array (
    'type' => 'int(11)',
  ),
  'height' => 
  array (
    'type' => 'int(11)',
  ),
  'created_at' => 
  array (
    'type' => 'datetime',
  ),
  'rating' => 
  array (
    'type' => 'char(1)',
  ),
  'preview_width' => 
  array (
    'type' => 'int(3)',
  ),
  'preview_height' => 
  array (
    'type' => 'int(3)',
  ),
  'actual_preview_width' => 
  array (
    'type' => 'int(3)',
  ),
  'actual_preview_height' => 
  array (
    'type' => 'int(3)',
  ),
  'score' => 
  array (
    'type' => 'int(3)',
  ),
  'is_shown_in_index' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'is_held' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'has_children' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'status' => 
  array (
    'type' => 'enum(\'deleted\',\'flagged\',\'pending\',\'active\')',
  ),
  'is_rating_locked' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'is_note_locked' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'parent_id' => 
  array (
    'type' => 'int(11)',
  ),
  'sample_width' => 
  array (
    'type' => 'int(5)',
  ),
  'sample_height' => 
  array (
    'type' => 'int(5)',
  ),
  'sample_size' => 
  array (
    'type' => 'int(11)',
  ),
  'index_timestamp' => 
  array (
    'type' => 'datetime',
  ),
  'jpeg_width' => 
  array (
    'type' => 'int(11)',
  ),
  'jpeg_height' => 
  array (
    'type' => 'int(11)',
  ),
  'jpeg_size' => 
  array (
    'type' => 'int(11)',
  ),
  'random' => 
  array (
    'type' => 'int(11)',
  ),
  'approver_id' => 
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