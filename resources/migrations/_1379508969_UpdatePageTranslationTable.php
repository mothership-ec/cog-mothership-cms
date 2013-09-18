<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1379508969_UpdatePageTranslationTable extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE `page_translation`
			DROP `language_id`,
			DROP `country_id`,
			ADD `locale` varchar(50) NOT NULL DEFAULT ''
		");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE `page_translation`
			DROP `locale`,
			ADD `language_id` char(2) NOT NULL DEFAULT '',
			ADD `country_id` char(2) DEFAULT ''
		");
	}
}