<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\ContentLoader;
use Message\Cog\DB\Adapter\Faux\Connection;
use Message\Cog\DB\Query;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Loader;
use Message\Mothership\CMS\Page\Edit;
use Message\Mothership\User\User;
use Message\Cog\Test\Event\FauxDispatcher;
use Message\Cog\ValueObject\DateTimeImmutable;


class EditTest extends \PHPUnit_Framework_TestCase
{
	protected $_nestedSetHelper;

	public function testSave()
	{
		$connection = new Connection(array('affectedRows' => 1));
		// For testDuplicateFieldNameException
		$connection->setPattern('/page_id([\s]+?)IN \(1\)/', array(
			array(
				'id'			=> 1,
				'publishAt'     => strtotime('-1 Day'),
				'unpublishAt'   => strtotime('+1 day'),
				'createdBy'     => time(),
				'createdAt'     => time(),
				'deletedBy'		=> null,
				'deletedAt'		=> null,
				'updatedBy'     => null,
				'updatedAt'     => null,
				'slug'			=> '/blog/hello-world',
			),
		));
		$loader = new Loader('gb', new Query($connection));
		$page = $loader->getByID(1);
		$despatcher = new FauxDispatcher;
		$this->_nestedSetHelper = $this->getMock('Message\Cog\DB\NestedSetHelper', array('insertChildAtEnd'), array(
			 new Query($connection),
			$this->getMock('Message\Cog\DB\Transaction', array(), array(), '', false)
		));
		$connection->setPattern('/page_id([\s]+?)= 1/', array(
			array(
				'id'			=> 1,
				'publishAt'     => strtotime('-1 Day'),
				'unpublishAt'   => strtotime('+1 day'),
				'createdBy'     => time(),
				'createdAt'     => time(),
				'deletedBy'		=> null,
				'deletedAt'		=> null,
				'updatedBy'     => null,
				'updatedAt'     => null,
				'slug'			=> '/blog/hello-world',
			),
		));
		$edit = new Edit($loader, new Query($connection), $despatcher, $this->_nestedSetHelper);
		$page->title = 'updated';

		$returnedPage = $edit->save($page);
		$this->assertInstanceOf('Message\Mothership\CMS\Page\Page', $returnedPage);

		$date = new DateTimeImmutable;
		$this->assertEquals(
			$returnedPage->authorship->updatedAt()->getTimestamp(),
			$date->getTimestamp(), 2
		);
	}

	public function testPublish()
	{
		$connection = new Connection(array('affectedRows' => 1));
		// For testDuplicateFieldNameException
		$connection->setPattern('/page_id([\s]+?)IN \(1\)/', array(
			array(
				'id'			=> 1,
				'publishAt'     => strtotime('-2 Day'),
				'unpublishAt'   => strtotime('-1 day'),
				'createdBy'     => time(),
				'createdAt'     => time(),
				'deletedBy'		=> null,
				'deletedAt'		=> null,
				'updatedBy'     => null,
				'updatedAt'     => null,
				'slug'			=> '/blog/hello-world',
			),
		));
		$connection->setPattern('/page_id([\s]+?)= 1/', array(
			array(
				'id'			=> 1,
				'publishAt'     => strtotime('-1 Day'),
				'unpublishAt'   => strtotime('+1 day'),
				'createdBy'     => time(),
				'createdAt'     => time(),
				'deletedBy'		=> null,
				'deletedAt'		=> null,
				'updatedBy'     => null,
				'updatedAt'     => null,
				'slug'			=> '/blog/hello-world',
			),
		));

		$loader = new Loader('gb', new Query($connection));
		$page = $loader->getByID(1);

		$despatcher = new FauxDispatcher;
		$edit = new Edit($loader, new Query($connection), $despatcher);
		$returnedPage = $edit->publish($page, null);

		$date = new DateTimeImmutable;

		$this->assertInstanceOf(
			'Message\Cog\ValueObject\DateTimeImmutable',
			$returnedPage->publishDateRange->getStart()
		);

		$this->assertEquals(
			$returnedPage->publishDateRange->getStart()->getTimestamp(),
			$date->getTimestamp(),
			2
		);
	}

	public function testUnpublish()
	{
		$connection = new Connection(array('affectedRows' => 1));
		// For testDuplicateFieldNameException
		$connection->setPattern('/page_id([\s]+?)IN \(1\)/', array(
			array(
				'id'			=> 1,
				'publishAt'     => strtotime('+2 Day'),
				'unpublishAt'   => strtotime('+31 day'),
				'createdBy'     => time(),
				'createdAt'     => time(),
				'deletedBy'		=> null,
				'deletedAt'		=> null,
				'updatedBy'     => null,
				'updatedAt'     => null,
				'slug'			=> '/blog/hello-world',
			),
		));
		$connection->setPattern('/page_id([\s]+?)= 1/', array(
			array(
				'id'			=> 1,
				'publishAt'     => strtotime('-1 Day'),
				'unpublishAt'   => strtotime('+1 day'),
				'createdBy'     => time(),
				'createdAt'     => time(),
				'deletedBy'		=> null,
				'deletedAt'		=> null,
				'updatedBy'     => null,
				'updatedAt'     => null,
				'slug'			=> '/blog/hello-world',
			),
		));
		$loader = new Loader('gb', new Query($connection));
		$page = $loader->getByID(1);

		$despatcher = new FauxDispatcher;
		$edit = new Edit($loader, new Query($connection), $despatcher);
		$returnedPage = $edit->unpublish($page, null);

		$date = new DateTimeImmutable;

		$this->assertNull(
			$returnedPage->publishDateRange->getStart()
		);
		$this->assertInstanceOf(
			'Message\Cog\ValueObject\DateTimeImmutable',
			$returnedPage->publishDateRange->getEnd()
		);

		$this->assertEquals(
			$returnedPage->publishDateRange->getEnd()->getTimestamp(),
			$date->getTimestamp(),
			2
		);

	}
}