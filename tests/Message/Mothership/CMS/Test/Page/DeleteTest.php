<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\ContentLoader;

use Message\Cog\DB\Adapter\Faux\Connection;
use Message\Cog\DB\Query;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Loader;

use Message\Mothership\CMS\Page\Delete as PageDelete;

class DeleteTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
  		$delete = new PageDelete($this->_services['db.query'],$this->_services['event.dispatcher'], $this->_services['user.current']);
	}

	public function testDelete()
	{
<<<<<<< HEAD
		$loader = new Loader('gb', new Query($connection));
		$page = $loader->getByID(1);
		
		$delete->delete($page);

		$this->assertNotNull($page->authorship->deletedAt()->getTimestamp());
	}
	
	public function testRestore()
	{
		
	}

	public function testForChildrenPagesException()
	{
=======
       $this->markTestIncomplete(
          'This test has not been implemented yet.'
        );
>>>>>>> b7e54c44e9089efb3dbfd069d62933031fcb93bc
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
<<<<<<< HEAD

		$delete->delete($page);

		$this->assertThrowsException('Cannot delete page #%i because it has children pages');
=======
		$loader = new Loader('gb', new Query($connection));
		$page = $loader->getByID(1);


	}

	public function testRestore()
	{

>>>>>>> b7e54c44e9089efb3dbfd069d62933031fcb93bc
	}
}
