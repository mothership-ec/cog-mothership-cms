<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Loader;
use Message\Cog\DB\Adapter\Faux\ConnectionCsv;
use Message\Cog\DB\Query;

class LoaderTest extends \PHPUnit_Framework_TestCase
{

	public function testGetByID()
	{
		$paths = array(
			__DIR__.'/Data/full_table_results.csv',
			__DIR__.'/Data/empty_table.csv',
		);

		$loader = $this->_getLoader($paths);

		// test that the corrcet instance is returned for a single ID
		$page = $loader->getByID(1);
		$this->assertTrue($page instanceof Page);

		// test that an array of Page objects are returned if an array of them are 
		// passed through
		$page = $loader->getByID(0);
		$this->assertFalse($page);
		
	}
	
	public function testGetBySlug()
	{

		$paths = array(
			__DIR__.'/Data/page_table.csv',
			__DIR__.'/Data/full_table_results.csv',
			__DIR__.'/Data/page_id_empty.csv',
			__DIR__.'/Data/historical_result.csv',
			__DIR__.'/Data/full_table_results.csv',

		);

		// Check that a page instance is returned for a valid slug from a current page with history false
		$loader = $this->_getLoader($paths);
		
		$page = $loader->getBySlug('/blog/hello-world');
		$this->assertTrue($page instanceof Page);


		$page = $loader->getBySlug('/blog/hello-world-old', true);
		$this->assertTrue($page instanceof Page);

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
	
	protected function _getLoader(array $paths)
	{
		$connection = new ConnectionCsv;
		$connection->setSequence($paths);
		$query = new Query($connection);
		
		return new \Message\Mothership\CMS\Page\Loader('gb', $query);
	}

}