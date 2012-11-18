<?php
return array (
  0 => 'CREATE TABLE IF NOT EXISTS `artists` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `alias_id` int(11) DEFAULT NULL,
  `group_id` int(11) DEFAULT NULL,
  `name` varchar(128) NOT NULL,
  `updated_at` datetime NOT NULL,
  `updater_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_artists__updater_id` (`updater_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  1 => 'CREATE TABLE IF NOT EXISTS `artists_urls` (
  `artist_id` int(11) NOT NULL,
  `url` varchar(256) NOT NULL,
  `normalized_url` varchar(256) NOT NULL,
  KEY `artist_id` (`artist_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
  2 => 'CREATE TABLE IF NOT EXISTS `bans` (
  `user_id` int(11) NOT NULL,
  `reason` text NOT NULL,
  `expires_at` datetime NOT NULL,
  `banned_by` int(11) NOT NULL,
  `old_level` int(11) NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `fk_bans__banned_by` (`banned_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
  3 => 'CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_addr` varchar(16) NOT NULL,
  `created_at` datetime DEFAULT \'0000-00-00 00:00:00\',
  `body` text NOT NULL,
  `updated_at` datetime NOT NULL,
  `is_spam` tinyint(1) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`id`),
  KEY `fk_comments__user_id` (`user_id`),
  KEY `fk_comments__post_id` (`post_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  4 => 'CREATE TABLE IF NOT EXISTS `dmails` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from_id` int(11) NOT NULL,
  `to_id` int(11) NOT NULL,
  `title` text NOT NULL,
  `body` text NOT NULL,
  `created_at` datetime DEFAULT NULL,
  `has_seen` tinyint(1) NOT NULL DEFAULT \'0\',
  `parent_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `from_id` (`from_id`,`to_id`),
  KEY `fk_dmails__to_id` (`to_id`),
  KEY `fk_dmails__parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  5 => 'CREATE TABLE IF NOT EXISTS `favorites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `post_id__user_id` (`post_id`,`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  6 => 'CREATE TABLE IF NOT EXISTS `flagged_post_details` (
  `created_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `reason` varchar(512) NOT NULL,
  `is_resolved` tinyint(1) NOT NULL DEFAULT \'0\',
  KEY `post_id` (`post_id`),
  KEY `fk_flag_post_details__user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
  7 => 'CREATE TABLE IF NOT EXISTS `forum_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `title` text NOT NULL,
  `body` text NOT NULL,
  `creator_id` int(11) NOT NULL,
  `parent_id` int(11) DEFAULT NULL,
  `last_updated_by` int(11) DEFAULT NULL,
  `is_sticky` tinyint(1) NOT NULL DEFAULT \'0\',
  `response_count` int(11) NOT NULL DEFAULT \'0\',
  `is_locked` tinyint(1) NOT NULL DEFAULT \'0\',
  `text_search_index` text,
  PRIMARY KEY (`id`),
  KEY `fk_forum_posts__creator_id` (`creator_id`),
  KEY `fk_forum_posts__last_updated_by` (`last_updated_by`),
  KEY `fk_forum_posts__parent_id` (`parent_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  8 => 'CREATE TABLE IF NOT EXISTS `ip_bans` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `expires_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `ip_addr` varchar(15) NOT NULL,
  `reason` text NOT NULL,
  `banned_by` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_ip_bans__banned_by` (`banned_by`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ',
  9 => 'CREATE TABLE IF NOT EXISTS `job_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `task_type` varchar(64) NOT NULL,
  `data_as_json` text,
  `status` varchar(64) NOT NULL,
  `status_message` text,
  `repeat_count` int(11) NOT NULL DEFAULT \'0\',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  10 => 'CREATE TABLE IF NOT EXISTS `notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `updated_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `user_id` int(11) NOT NULL,
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `ip_addr` varchar(64) NOT NULL,
  `version` int(11) NOT NULL DEFAULT \'1\',
  `is_active` tinyint(1) NOT NULL DEFAULT \'1\',
  `post_id` int(11) NOT NULL,
  `body` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  11 => 'CREATE TABLE IF NOT EXISTS `note_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `updated_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `x` int(11) NOT NULL,
  `y` int(11) NOT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `body` text NOT NULL,
  `version` int(11) NOT NULL,
  `ip_addr` varchar(64) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT \'1\',
  `note_id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `fk_note_versions__note_id` (`note_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  12 => 'CREATE TABLE IF NOT EXISTS `pools` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `description` varchar(128) NOT NULL,
  `user_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT \'1\',
  `created_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `updated_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `post_count` int(3) NOT NULL DEFAULT \'0\',
  `is_public` binary(1) NOT NULL DEFAULT \'1\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `pool_name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  13 => 'CREATE TABLE IF NOT EXISTS `pools_posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `pool_id` int(11) NOT NULL,
  `sequence` varchar(16) NOT NULL,
  `next_post_id` int(11) DEFAULT NULL,
  `prev_post_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `post_id` (`post_id`),
  KEY `fk_pools_posts__next_post_id` (`next_post_id`),
  KEY `fk_pools_posts__prev_post_id` (`prev_post_id`),
  KEY `fk_pools_posts__pool_id` (`pool_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  14 => 'DROP TRIGGER IF EXISTS `pools_posts_delete_trg`',
  15 => 'CREATE TRIGGER `pools_posts_delete_trg` BEFORE DELETE ON `pools_posts`
 FOR EACH ROW BEGIN
  UPDATE pools SET post_count = post_count - 1 WHERE id = OLD.pool_id;
END',
  16 => 'DROP TRIGGER IF EXISTS `pools_posts_insert_trg`',
  17 => 'CREATE TRIGGER `pools_posts_insert_trg` BEFORE INSERT ON `pools_posts`
 FOR EACH ROW BEGIN
  UPDATE pools SET post_count = post_count + 1 WHERE id = NEW.pool_id;
END',
  18 => 'CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `ip_addr` varchar(64) NOT NULL,
  `file_size` int(11) NOT NULL,
  `md5` varchar(32) NOT NULL,
  `last_commented_at` datetime DEFAULT NULL,
  `file_ext` varchar(4) NOT NULL,
  `last_noted_at` datetime DEFAULT NULL,
  `source` varchar(249) DEFAULT NULL,
  `width` int(11) NOT NULL,
  `height` int(11) NOT NULL,
  `created_at` datetime NOT NULL,
  `rating` char(1) NOT NULL DEFAULT \'q\',
  `preview_width` int(3) NOT NULL,
  `preview_height` int(3) NOT NULL,
  `actual_preview_width` int(3) NOT NULL,
  `actual_preview_height` int(3) NOT NULL,
  `score` int(3) NOT NULL DEFAULT \'0\',
  `is_shown_in_index` tinyint(1) NOT NULL DEFAULT \'1\',
  `is_held` tinyint(1) NOT NULL DEFAULT \'0\',
  `has_children` tinyint(1) NOT NULL DEFAULT \'0\',
  `status` enum(\'deleted\',\'flagged\',\'pending\',\'active\') CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT \'active\',
  `is_rating_locked` tinyint(1) NOT NULL DEFAULT \'0\',
  `is_note_locked` tinyint(1) NOT NULL DEFAULT \'0\',
  `parent_id` int(11) DEFAULT NULL,
  `sample_width` int(5) DEFAULT NULL,
  `sample_height` int(5) DEFAULT NULL,
  `sample_size` int(11) DEFAULT NULL,
  `index_timestamp` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `jpeg_width` int(11) DEFAULT NULL,
  `jpeg_height` int(11) DEFAULT NULL,
  `jpeg_size` int(11) DEFAULT NULL,
  `random` int(11) NOT NULL,
  `approver_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `md5` (`md5`),
  KEY `fk_posts__parent_id` (`parent_id`),
  KEY `posts__approver_id` (`approver_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  19 => 'DROP TRIGGER IF EXISTS `trg_posts__delete`',
  20 => 'CREATE TRIGGER `trg_posts__delete` AFTER DELETE ON `posts`
 FOR EACH ROW BEGIN
  UPDATE table_data SET row_count = row_count - 1 WHERE name = \'posts\';
END',
  21 => 'DROP TRIGGER IF EXISTS `trg_posts__insert`',
  22 => 'CREATE TRIGGER `trg_posts__insert` AFTER INSERT ON `posts`
 FOR EACH ROW BEGIN
  UPDATE table_data SET row_count = row_count + 1 WHERE name = \'posts\';
END',
  23 => 'CREATE TABLE IF NOT EXISTS `posts_tags` (
  `post_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL,
  UNIQUE KEY `post_id` (`post_id`,`tag_id`),
  KEY `fk_posts_tags__post_id` (`post_id`),
  KEY `fk_posts_tags__tag_id` (`tag_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
  24 => 'DROP TRIGGER IF EXISTS `trg_posts_tags__delete`',
  25 => 'CREATE TRIGGER `trg_posts_tags__delete` AFTER DELETE ON `posts_tags`
 FOR EACH ROW BEGIN
 UPDATE tags SET post_count = post_count - 1 WHERE tags.id = OLD.tag_id;
END',
  26 => 'DROP TRIGGER IF EXISTS `trg_posts_tags__insert`',
  27 => 'CREATE TRIGGER `trg_posts_tags__insert` AFTER INSERT ON `posts_tags`
 FOR EACH ROW BEGIN
  UPDATE tags SET post_count = post_count + 1 WHERE tags.id = NEW.tag_id;
END',
  28 => 'CREATE TABLE IF NOT EXISTS `post_votes` (
  `post_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(1) DEFAULT \'0\',
  `updated_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  UNIQUE KEY `post_id` (`post_id`,`user_id`),
  KEY `score` (`score`),
  KEY `fk_user_id__users_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
  29 => 'CREATE TABLE IF NOT EXISTS `table_data` (
  `name` varchar(11) CHARACTER SET ucs2 NOT NULL,
  `row_count` int(11) NOT NULL DEFAULT \'0\',
  KEY `name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
  30 => 'CREATE TABLE IF NOT EXISTS `tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `post_count` int(11) NOT NULL DEFAULT \'0\',
  `cached_related` text,
  `cached_related_expires_on` datetime DEFAULT NULL,
  `tag_type` smallint(6) NOT NULL,
  `is_ambiguous` tinyint(1) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  31 => 'CREATE TABLE IF NOT EXISTS `tag_aliases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(64) NOT NULL,
  `alias_id` int(11) NOT NULL,
  `is_pending` tinyint(1) NOT NULL DEFAULT \'0\',
  `reason` varchar(128) DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `alias_unique` (`name`,`alias_id`),
  KEY `name` (`name`),
  KEY `fk_tag_aliases__alias_id` (`alias_id`),
  KEY `fk_tag_aliases__creator_id` (`creator_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  32 => 'CREATE TABLE IF NOT EXISTS `tag_implications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `predicate_id` int(11) NOT NULL,
  `consequent_id` int(11) NOT NULL,
  `is_pending` tinyint(1) NOT NULL DEFAULT \'0\',
  `reason` varchar(128) DEFAULT NULL,
  `creator_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `predicate_id__consequent_id` (`predicate_id`,`consequent_id`),
  KEY `fk_tag_implications__consequent_id` (`consequent_id`),
  KEY `fk_tag_implications__creator_id` (`creator_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  33 => 'CREATE TABLE IF NOT EXISTS `tag_subscriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `tag_query` text CHARACTER SET latin1 NOT NULL,
  `cached_post_ids` text CHARACTER SET latin1 NOT NULL,
  `name` varchar(32) CHARACTER SET latin1 NOT NULL,
  `is_visible_on_profile` tinyint(1) NOT NULL DEFAULT \'1\',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `name` (`name`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  34 => 'CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(32) NOT NULL,
  `password_hash` varchar(40) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `level` int(11) NOT NULL DEFAULT \'20\',
  `email` varchar(249) DEFAULT NULL,
  `avatar_post_id` int(11) DEFAULT NULL,
  `avatar_width` double DEFAULT NULL,
  `avatar_height` double DEFAULT NULL,
  `avatar_top` double DEFAULT NULL,
  `avatar_bottom` double DEFAULT NULL,
  `avatar_left` double DEFAULT NULL,
  `avatar_right` double DEFAULT NULL,
  `avatar_timestamp` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `my_tags` text,
  `show_samples` tinyint(1) NOT NULL DEFAULT \'1\',
  `show_advanced_editing` tinyint(1) NOT NULL DEFAULT \'0\',
  `pool_browse_mode` tinyint(1) NOT NULL DEFAULT \'0\',
  `use_browser` tinyint(1) NOT NULL DEFAULT \'0\',
  `always_resize_images` tinyint(1) NOT NULL DEFAULT \'0\',
  `last_logged_in_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `last_forum_topic_read_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `last_comment_read_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `last_deleted_post_seen_at` datetime NOT NULL DEFAULT \'0000-00-00 00:00:00\',
  `language` text,
  `secondary_languages` text,
  `receive_dmails` tinyint(1) NOT NULL DEFAULT \'1\',
  `has_mail` tinyint(1) NOT NULL DEFAULT \'0\',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`),
  KEY `fk_users__avatar_post_id` (`avatar_post_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  35 => 'CREATE TABLE IF NOT EXISTS `user_blacklisted_tags` (
  `user_id` int(11) NOT NULL,
  `tags` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  UNIQUE KEY `user_id` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8',
  36 => 'CREATE TABLE IF NOT EXISTS `wiki_pages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `version` int(11) NOT NULL DEFAULT \'1\',
  `title` varchar(64) NOT NULL,
  `body` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_addr` varchar(15) NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT \'0\',
  `text_search_index` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  37 => 'CREATE TABLE IF NOT EXISTS `wiki_page_versions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `created_at` datetime NOT NULL,
  `updated_at` datetime NOT NULL,
  `version` int(11) NOT NULL DEFAULT \'1\',
  `title` varchar(64) NOT NULL,
  `body` text NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `ip_addr` varchar(15) NOT NULL,
  `wiki_page_id` int(11) NOT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT \'0\',
  `text_search_index` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `fk_wiki_page_versions__wiki_page` (`wiki_page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ',
  38 => 'ALTER TABLE `artists`
  ADD CONSTRAINT `fk_artists__updater_id` FOREIGN KEY (`updater_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
  39 => 'ALTER TABLE `artists_urls`
  ADD CONSTRAINT `fk_artists_urls__artist_id` FOREIGN KEY (`artist_id`) REFERENCES `artists` (`id`) ON DELETE CASCADE',
  40 => 'ALTER TABLE `bans`
  ADD CONSTRAINT `fk_bans__banned_by` FOREIGN KEY (`banned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_bans__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
  41 => 'ALTER TABLE `comments`
  ADD CONSTRAINT `fk_comments__post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_comments__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
  42 => 'ALTER TABLE `dmails`
  ADD CONSTRAINT `fk_dmails__from_id` FOREIGN KEY (`from_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dmails__parent_id` FOREIGN KEY (`parent_id`) REFERENCES `dmails` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_dmails__to_id` FOREIGN KEY (`to_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
  43 => 'ALTER TABLE `flagged_post_details`
  ADD CONSTRAINT `fk_flag_post_details__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_flag_post_det__post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE',
  44 => 'ALTER TABLE `forum_posts`
  ADD CONSTRAINT `fk_forum_posts__creator_id` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_forum_posts__last_updated_by` FOREIGN KEY (`last_updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_forum_posts__parent_id` FOREIGN KEY (`parent_id`) REFERENCES `forum_posts` (`id`) ON DELETE CASCADE',
  45 => 'ALTER TABLE `ip_bans`
  ADD CONSTRAINT `fk_ip_bans__banned_by` FOREIGN KEY (`banned_by`) REFERENCES `users` (`id`) ON DELETE CASCADE',
  46 => 'ALTER TABLE `note_versions`
  ADD CONSTRAINT `fk_note_versions__note_id` FOREIGN KEY (`note_id`) REFERENCES `notes` (`id`) ON DELETE CASCADE',
  47 => 'ALTER TABLE `pools_posts`
  ADD CONSTRAINT `fk_pools_posts__next_post_id` FOREIGN KEY (`next_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_pools_posts__pool_id` FOREIGN KEY (`pool_id`) REFERENCES `pools` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pools_posts__post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_pools_posts__prev_post_id` FOREIGN KEY (`prev_post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL',
  48 => 'ALTER TABLE `posts`
  ADD CONSTRAINT `fk_parent_id__posts_id` FOREIGN KEY (`parent_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `posts__approver_id` FOREIGN KEY (`approver_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
  49 => 'ALTER TABLE `posts_tags`
  ADD CONSTRAINT `fk_posts_tags__post_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_posts_tags__tag_id` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE',
  50 => 'ALTER TABLE `post_votes`
  ADD CONSTRAINT `fk_post_id__posts_id` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_id__users_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
  51 => 'ALTER TABLE `tag_aliases`
  ADD CONSTRAINT `fk_tag_aliases__alias_id` FOREIGN KEY (`alias_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tag_aliases__creator_id` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
  52 => 'ALTER TABLE `tag_implications`
  ADD CONSTRAINT `fk_consequent_id` FOREIGN KEY (`consequent_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tag_implications__consequent_id` FOREIGN KEY (`consequent_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tag_implications__creator_id` FOREIGN KEY (`creator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_tag_implications__predicate_id` FOREIGN KEY (`predicate_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE',
  53 => 'ALTER TABLE `user_blacklisted_tags`
  ADD CONSTRAINT `fk_user_bl_tags__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE',
  54 => 'ALTER TABLE `wiki_pages`
  ADD CONSTRAINT `fk_wiki_pages__user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL',
  55 => 'ALTER TABLE `wiki_page_versions`
  ADD CONSTRAINT `fk_wiki_page_versions__wiki_page` FOREIGN KEY (`wiki_page_id`) REFERENCES `wiki_pages` (`id`) ON DELETE CASCADE
',
);