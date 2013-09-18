<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379413937_SetUp extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE `page` (
			  `page_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `deleted_at` int(11) unsigned DEFAULT NULL,
			  `deleted_by` int(11) unsigned DEFAULT NULL,
			  `title` varchar(255) NOT NULL,
			  `publish_at` int(11) unsigned DEFAULT NULL,
			  `unpublish_at` int(11) unsigned DEFAULT NULL,
			  `slug` varchar(255) NOT NULL DEFAULT '',
			  `position_left` int(11) unsigned DEFAULT NULL,
			  `position_right` int(11) unsigned DEFAULT NULL,
			  `position_depth` int(11) unsigned DEFAULT NULL,
			  `visibility_search` tinyint(1) unsigned NOT NULL DEFAULT '1',
			  `visibility_menu` tinyint(1) unsigned NOT NULL DEFAULT '1',
			  `visibility_aggregator` tinyint(1) unsigned NOT NULL DEFAULT '1',
			  `password` varchar(255) DEFAULT NULL,
			  `type` varchar(255) NOT NULL DEFAULT '',
			  `comment_enabled` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `comment_access` int(3) NOT NULL DEFAULT '0',
			  `comment_expiry` int(11) unsigned DEFAULT NULL,
			  `comment_approval` tinyint(1) unsigned DEFAULT NULL,
			  `access` int(3) NOT NULL DEFAULT '0',
			  `meta_title` varchar(255) DEFAULT NULL,
			  `meta_description` text,
			  `meta_html_head` text,
			  `meta_html_foot` text,
			  `meta_title_inherit` tinyint(1) NOT NULL DEFAULT '0',
			  `meta_description_inherit` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `meta_html_head_inherit` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  `meta_html_foot_inherit` tinyint(1) unsigned NOT NULL DEFAULT '0',
			  PRIMARY KEY (`page_id`),
			  KEY `created_by` (`created_by`),
			  KEY `update_by` (`updated_by`),
			  KEY `deleted_by` (`deleted_by`),
			  KEY `comment_access` (`comment_access`),
			  KEY `access` (`access`),
			  KEY `position_left` (`position_left`),
			  KEY `position_right` (`position_right`),
			  KEY `position_depth` (`position_depth`),
			  KEY `visibility_search` (`visibility_search`),
			  KEY `visibility_menu` (`visibility_menu`),
			  KEY `visibility_aggregator` (`visibility_aggregator`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `page_access_group` (
			  `page_id` int(11) unsigned NOT NULL,
			  `group_name` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`page_id`,`group_name`),
			  KEY `page_id` (`page_id`),
			  KEY `group_id` (`group_name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `page_comment` (
			  `comment_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `deleted_at` int(11) unsigned DEFAULT NULL,
			  `deleted_by` int(11) unsigned DEFAULT NULL,
			  `page_id` int(11) unsigned NOT NULL,
			  `user_id` int(11) unsigned DEFAULT NULL,
			  `name` varchar(255) DEFAULT '',
			  `email` varchar(255) DEFAULT '',
			  `ip_address` varchar(45) NOT NULL DEFAULT '',
			  `website` varchar(255) DEFAULT '',
			  `body` text NOT NULL,
			  `reported_at` int(11) unsigned DEFAULT NULL,
			  `reported_by` int(11) unsigned DEFAULT NULL,
			  `approved_at` int(11) unsigned DEFAULT NULL,
			  `approved_by` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`comment_id`),
			  KEY `page_id` (`page_id`),
			  KEY `created_by` (`created_by`),
			  KEY `updated_by` (`updated_by`),
			  KEY `deleted_by` (`deleted_by`),
			  KEY `reported_by` (`reported_by`),
			  KEY `approved_by` (`approved_by`),
			  KEY `user_id` (`user_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `page_content` (
			  `page_id` int(11) unsigned NOT NULL,
			  `locale` varchar(50) NOT NULL DEFAULT '',
			  `field_name` varchar(255) NOT NULL DEFAULT '',
			  `value_string` text NOT NULL,
			  `value_int` int(11) DEFAULT NULL,
			  `group_name` varchar(255) NOT NULL DEFAULT '',
			  `sequence` int(11) unsigned NOT NULL DEFAULT '0',
			  `data_name` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`page_id`,`locale`,`field_name`,`group_name`,`sequence`,`data_name`),
			  KEY `language_id` (`locale`),
			  KEY `value_int` (`value_int`),
			  KEY `page_id` (`page_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `page_slug_history` (
			  `page_id` int(11) unsigned NOT NULL,
			  `slug` varchar(225) NOT NULL DEFAULT '',
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  PRIMARY KEY (`slug`,`page_id`),
			  KEY `page_id` (`page_id`),
			  KEY `created_by` (`created_by`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `page_tag` (
			  `page_id` int(11) unsigned NOT NULL,
			  `tag_name` varchar(255) NOT NULL DEFAULT '',
			  PRIMARY KEY (`tag_name`,`page_id`),
			  KEY `page_id` (`page_id`),
			  KEY `tag_name` (`tag_name`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `page_translation` (
			  `page_id` int(11) unsigned NOT NULL,
			  `language_id` char(2) NOT NULL DEFAULT '',
			  `country_id` char(2) DEFAULT '',
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `updated_at` int(11) unsigned DEFAULT NULL,
			  `updated_by` int(11) unsigned DEFAULT NULL,
			  `deleted_at` int(11) unsigned DEFAULT NULL,
			  `deleted_by` int(11) unsigned DEFAULT NULL,
			  `title` varchar(255) DEFAULT '',
			  `meta_title` varchar(255) DEFAULT '',
			  `meta_description` text,
			  PRIMARY KEY (`page_id`,`language_id`),
			  KEY `created_by` (`created_by`),
			  KEY `updated_by` (`updated_by`),
			  KEY `page_id` (`page_id`),
			  KEY `language_id` (`language_id`),
			  KEY `country_id` (`country_id`),
			  KEY `deleted_by` (`deleted_by`)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		$this->run("
			CREATE TABLE `search_log` (
			  `log_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			  `created_at` int(11) unsigned NOT NULL,
			  `created_by` int(11) unsigned DEFAULT NULL,
			  `term` varchar(511) NOT NULL DEFAULT '',
			  `referrer` varchar(511) DEFAULT NULL,
			  `ip_address` varchar(255) DEFAULT NULL,
			  PRIMARY KEY (`log_id`),
			  KEY `created_at` (`created_at`),
			  KEY `created_by` (`created_by`),
			  KEY `term` (`term`(255))
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run('
			DROP TABLE IF EXISTS
				`page`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`page_access_group`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`page_comment`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`page_content`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`page_slug_history`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`page_tag`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`page_translation`
		');

		$this->run('
			DROP TABLE IF EXISTS
				`search_log`
		');
	}
}
