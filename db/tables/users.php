<?php
$this->_columns = array (
  'id' => 
  array (
    'type' => 'int(11)',
  ),
  'name' => 
  array (
    'type' => 'varchar(32)',
  ),
  'password_hash' => 
  array (
    'type' => 'varchar(40)',
  ),
  'created_at' => 
  array (
    'type' => 'datetime',
  ),
  'level' => 
  array (
    'type' => 'int(11)',
  ),
  'email' => 
  array (
    'type' => 'varchar(249)',
  ),
  'avatar_post_id' => 
  array (
    'type' => 'int(11)',
  ),
  'avatar_width' => 
  array (
    'type' => 'double',
  ),
  'avatar_height' => 
  array (
    'type' => 'double',
  ),
  'avatar_top' => 
  array (
    'type' => 'double',
  ),
  'avatar_bottom' => 
  array (
    'type' => 'double',
  ),
  'avatar_left' => 
  array (
    'type' => 'double',
  ),
  'avatar_right' => 
  array (
    'type' => 'double',
  ),
  'avatar_timestamp' => 
  array (
    'type' => 'datetime',
  ),
  'my_tags' => 
  array (
    'type' => 'text',
  ),
  'show_samples' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'show_advanced_editing' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'pool_browse_mode' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'use_browser' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'always_resize_images' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'last_logged_in_at' => 
  array (
    'type' => 'datetime',
  ),
  'last_forum_topic_read_at' => 
  array (
    'type' => 'datetime',
  ),
  'last_comment_read_at' => 
  array (
    'type' => 'datetime',
  ),
  'last_deleted_post_seen_at' => 
  array (
    'type' => 'datetime',
  ),
  'language' => 
  array (
    'type' => 'text',
  ),
  'secondary_languages' => 
  array (
    'type' => 'text',
  ),
  'receive_dmails' => 
  array (
    'type' => 'tinyint(1)',
  ),
  'has_mail' => 
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