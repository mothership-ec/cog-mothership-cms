<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Loader;
use Message\Cog\DB\Adapter\Faux\ConnectionCsv;
use Message\Cog\DB\Query;

class LoaderTest extends \PHPUnit_Framework_TestCase
{

	protected $_loader;

	public function setUp()
	{
		$connection = new ConnectionCsv;
		$connection->setResult(__DIR__.'/page_table.csv');
		$query = new Query($connection);
		
		$this->_loader =  new \Message\Mothership\CMS\Page\Loader('gb', $query);
	}

	public function testGetByID()
	{
		$page = $this->_loader->getByID(1);
		
		var_dump($page); exit;
		// test that the corrcet instance is returned for a single ID
		// test that an array of Page objects are returned if an array of them are passed through
		
	}
	
	public function testGetBySlug()
	{
		// Check that a page instance is returned for a valid slug from a current page with history false
		// Check that a page instance is returned for a valid slug from a current page with history true
		// Check that a page instance is returned for a historical slug
		// test for false to be returned is no slug is found
	}

	public function testCheckSlugHistory()
	{
		// Check that a page instance is returned for a valid historical slug
		// Check that false is returned is there is one which cannot be found
	}

	public function testGetByType()
	{
		// Check that an array of valid Page objects are returned
		// Check that false is returned is nothing is found
	}

	public function testGetChildren()
	{
		// Check that the correct amount of items are returned and that they all contain the Page object in each array value
		// false should be returned if nothing is found
	}

	public function testGetSiblings()
	{
		// Test the correct amount of items are returned and that there is an array or Page items
		// Return false when nothing is found
	}

}