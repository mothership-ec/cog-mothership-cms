<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\Create;

use Message\Mothership\CMS\Test\PageType\Blog;

use Message\Cog\DB\Adapter\Faux\Connection as FauxConnection;
use Message\Cog\Test\Event\FauxDispatcher;

class CreateTest extends \PHPUnit_Framework_TestCase
{
	const NEW_PAGE_ID = 4;

	protected $_newPage;

	protected $_nestedSetHelper;
	protected $_eventDispatcher;
	protected $_query;

	protected $_loader;
	protected $_create;

	public function setUp()
	{
		$this->_eventDispatcher = new FauxDispatcher;
		$this->_query           = $this->getMock('Message\Cog\DB\Query', array('query'), array(
			new FauxConnection(array('insertId' => 4))
		));
		$this->_nestedSetHelper = $this->getMock('Message\Cog\DB\NestedSetHelper', array('insertChildAtEnd'));
		$this->_loader          = $this->getMock('Message\Mothership\CMS\Page\Loader', array('getByID'), array(), '', false);
		$this->_create          = new Create(
			$this->_loader,
			$this->_query,
			$this->_eventDispatcher,
			$this->_nestedSetHelper
		);

		$this->_newPage = $this->getMock('Message\Mothership\CMS\Page\Page');
		$this->_newPage->id = self::NEW_PAGE_ID;

		$this->_loader
			->expects($this->any())
			->method('getByID')
			->with(self::NEW_PAGE_ID)
			->will($this->returnValue($this->_newPage));
	}

	public function testParentSterileException()
	{
		// test exception thrown if the parent page type does not allow children
		$this->markTestIncomplete('This test should be written once the functionality for it has been implemented.');
	}

	public function testInsertQuery()
	{

	}

	public function testAddedToTree()
	{
		$parentID = 6;
		$trans = $this->getMock('Message\Cog\DB\Transaction', array('commit'), array(), '', false);

		// Set up expectation for adding to the tree with no parent
		$this->_nestedSetHelper
			->expects($this->at(0))
			->method('insertChildAtEnd')
			->with(self::NEW_PAGE_ID, false, true)
			->will($this->returnValue($trans));

		// Set up expectation for adding to the tree with a parent
		$this->_nestedSetHelper
			->expects($this->at(1))
			->method('insertChildAtEnd')
			->with(self::NEW_PAGE_ID, $parentID, true)
			->will($this->returnValue($trans));

		// Create a page with no parent
		$this->_create->create(new Blog, 'My new blog page');

		// Create a page with a parent
		$parent = $this->getMock('Message\Mothership\CMS\Page\Page');
		$parent->id = $parentID;

		$this->_create->create(new Blog, 'Another new blog page', $parent);
	}

	public function testEventDispatched()
	{
		// set up expectation on the mock
	}

	/**
	 * @depends testEventDispatched
	 */
	public function testCreateReturnsPageFromEvent()
	{

	}
}