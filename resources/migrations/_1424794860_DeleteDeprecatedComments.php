<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1424794860_DeleteDeprecatedComments extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE
				page
			DROP
				comment_enabled;
		");

		$this->run("
			ALTER TABLE
				page
			DROP
				comment_access;
		");

		$this->run("
			ALTER TABLE
				page
			DROP
				comment_expiry;
		");

		$this->run("
			ALTER TABLE
				page
			DROP
				comment_approval;
		");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				page
			ADD
				comment_enabled TINYINT(1) DEFAULT NULL
			AFTER
				`type`
			;
		");

		$this->run("
			ALTER TABLE
				page
			ADD
				comment_access INT(3) DEFAULT NULL
			AFTER
				comment_enabled
			;
		");

		$this->run("
			ALTER TABLE
				page
			ADD
				comment_expiry INT(11) NOT NULL DEFAULT 0
			AFTER
				comment_access
			;
		");

		$this->run("
			ALTER TABLE
				page
			ADD
				comment_approval TINYINT(1) NOT NULL DEFAULT 0
			AFTER
				comment_expiry
			;
		");
	}
}