<?php

namespace Message\Mothership\CMS\Test;

use Message\Mothership\CMS\PageTypeCollection;

class PageTypeCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		// test the stuff passed into constructor gets added
	}

	/**
	 * @depends testConstructor
	 */
	public function testIteration()
	{
		// test looping over the class works properly
	}

	public function testAdd()
	{
		$collection = new PageTypeCollection;
		$pageType   = new PageType\Blog;

		$collection->add($pageType);

		// test what I added is in there
	}

	public function testCount()
	{
		$collection = new PageTypeCollection;

		$this->assertEquals(0, count($collection));
		$this->assertEquals(0, $collection->count());

		$collection->add(new PageType\Blog);

		$this->assertEquals(1, count($collection));
		$this->assertEquals(1, $collection->count());

		$collection->add(new PageType\Blog);

		$this->assertEquals(2, count($collection));
		$this->assertEquals(2, $collection->count());
	}

	public function testGetIterator()
	{
		$collection = new PageTypeCollection;
		$this->assertInstanceOf('\Iterator', $collection->getIterator());
	}
}