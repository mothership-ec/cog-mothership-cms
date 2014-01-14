<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\ContentLoader;
use Message\Cog\DB\Adapter\Faux\Connection;
use Message\Cog\DB\Query;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Loader;


class DeleteTest extends \PHPUnit_Framework_TestCase
{

	public function testDelete()
	{
       $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
		$connection = new Connection;
		// For testDuplicateFieldNameException
		$connection->setPattern('/page_id([\s]+?)= 1/', array(
			array(
				'page_id'		=> 1,
				'deletedAt'     => null,
				'deletedBy'     => null,
				'publishAt'     => strtotime('-1 Day'),
				'unpublishAt'   => strtotime('+1 day'),
				'createdBy'     => time(),
				'createdAt'     => time(),
				'updatedBy'     => null,
				'updatedAt'     => null,
				'slug'			=> '/blog/hello-world',
			),
			array(
				'page_id'		=> 1,
				'deletedAt'     => null,
				'deletedBy'     => null,
				'publishAt'     => strtotime('-1 Day'),
				'unpublishAt'   => strtotime('+1 day'),
				'createdBy'     => time(),
				'createdAt'     => time(),
				'updatedBy'     => null,
				'updatedAt'     => null,
				'slug'			=> '/blog/hello-world',
			),
		));
		$loader = new Loader('gb', new Query($connection));
		$page = $loader->getByID(1);


	}

	public function testRestore()
	{

	}
}
