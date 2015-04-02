<?php

use Message\Cog\Migration\Adapter\MySQL\Migration;

class _1427287552_MetaImage extends Migration
{
	public function up()
	{
		$this->run("
			ALTER TABLE
				page
			ADD
				meta_image INTEGER DEFAULT NULL
			AFTER
				`meta_description`
			;
		");
	}

	public function down()
	{
		$this->run("
			ALTER TABLE
				page
			DROP
				meta_image
			;
		");
	}
}