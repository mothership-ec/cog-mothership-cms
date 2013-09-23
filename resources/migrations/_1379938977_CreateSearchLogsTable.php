<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379938977_CreateSearchLogsTable extends Migration
{
	public function up()
	{
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
			) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8;
		");
	}

	public function down()
	{
		$this->run("
			DROP TABLE IF EXISTS `search_log`
		");
	}
}