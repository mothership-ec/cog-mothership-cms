<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1416224353_CommentTable extends Migration
{
	public function up()
	{
		$this->run("
			CREATE TABLE `blog_comment`
			(
				comment_id INT(11) NOT NULL AUTO_INCREMENT,
				page_id INT(11) NOT NULL,
				user_id INT(11) DEFAULT NULL,
				`name` VARCHAR(255) NOT NULL,
				email_address VARCHAR(255),
				website VARCHAR(255) DEFAULT NULL,
				content LONGTEXT NOT NULL,
				ip_address VARCHAR(15) NOT NULL,
				created_at INT(11) NOT NULL,
				updated_at INT(11) NOT NULL,
				updated_by INT(11) DEFAULT NULL,
				status VARCHAR(20) NOT NULL,
				PRIMARY KEY (comment_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");

		// Delete unused pre-existing comment table
		$this->run("DROP TABLE page_comment");
	}

	public function down()
	{
		$this->run("DROP TABLE blog_comment");

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
				)
			ENGINE=InnoDB DEFAULT CHARSET=utf8;
		");
	}
}