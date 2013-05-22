<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\ContentLoader;

use Message\Cog\DB\Adapter\Faux\Connection;
use Message\Cog\DB\Query;

class ContentLoaderTest extends \PHPUnit_Framework_TestCase
{
	protected $_loader;

	public function setUp()
	{
		$connection = new Connection;
		// For testFieldGroupNameClashException
		$connection->setPattern('/page_id([\s\t]+?)= 1/', array(
			array(
				'field'     => 'myField',
				'value'     => 'My Value',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => 'part1',
			),
			array(
				'field'     => 'myField',
				'value'     => 'Some name',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => 'part2',
			),
			array(
				'field'     => 'myField',
				'value'     => 'Some name',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
		));
		// For testFieldGroupNameCollisionException
		$connection->setPattern('/page_id([\s\t]+?)= 2/', array(
			array(
				'field'     => 'title',
				'value'     => 'My special title',
				'group'     => 'special',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'body',
				'value'     => 'Some name',
				'group'     => 'special',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'special',
				'value'     => 'Special field, not in a group',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
		));
		// For testMultipleValueFieldOnNormalFieldException
		$connection->setPattern('/page_id([\s\t]+?)= 3/', array(
			array(
				'field'     => 'title',
				'value'     => 'My special title',
				'group'     => 'special',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'body',
				'value'     => 'Some name',
				'group'     => 'special',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'special',
				'value'     => 'Special field, not in a group',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
		));
		$connection->setPattern('/page_id = 5/', array(
			array(
				'field'     => 'title',
				'value'     => 'This is my page\'s title.',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
		));

		$this->_loader = new ContentLoader(new Query($connection));
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage name collision on `myField`
	 */
	public function testDuplicateFieldNameException()
	{
		$page = $this->getMock('Message\Mothership\CMS\Page\Page');
		$page->id         = 1;
		$page->languageID = 'EN';
		$page->countryID  = null;

		$this->_loader->load($page);
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage name collision on `special`
	 */
	public function testFieldGroupNameCollisionException()
	{
		$page = $this->getMock('Message\Mothership\CMS\Page\Page');
		$page->id         = 2;
		$page->languageID = 'EN';
		$page->countryID  = null;

		$this->_loader->load($page);
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage Cannot set additional field value on single-value field `testField`
	 */
	public function testMultipleValueFieldOnNormalFieldException()
	{
		$page = $this->getMock('Message\Mothership\CMS\Page\Page');
		$page->id         = 3;
		$page->languageID = 'EN';
		$page->countryID  = null;

		$this->_loader->load($page);
	}
}