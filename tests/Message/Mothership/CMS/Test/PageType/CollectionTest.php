<?php

namespace Message\Mothership\CMS\Test\PageType;

use Message\Mothership\CMS\PageType\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructorAndIteration()
	{
		$pageTypes = array(
			new Blog,
			new Blog,
			new Blog,
			new Blog,
			new Blog,
		);
		$collection = new PageTypeCollection($pageTypes);

		$this->assertEquals(count($pageTypes), $collection->count());

		foreach ($collection as $key => $pageType) {
			$this->assertEquals($pageTypes[$key], $pageType);
		}
	}

	public function testAdd()
	{
		$collection = new PageTypeCollection;
		$pageType   = new Blog;

		$this->assertEquals($collection, $collection->add($pageType));

		foreach ($collection as $name => $type) {
			$this->assertEquals($pageType, $type);
		}
	}

	public function testCount()
	{
		$collection = new PageTypeCollection;

		$this->assertEquals(0, count($collection));
		$this->assertEquals(0, $collection->count());

		$collection->add(new Blog);

		$this->assertEquals(1, count($collection));
		$this->assertEquals(1, $collection->count());

		$collection->add(new Blog);

		$this->assertEquals(2, count($collection));
		$this->assertEquals(2, $collection->count());
	}

	public function testGetIterator()
	{
		$collection = new PageTypeCollection;
		$this->assertInstanceOf('\Iterator', $collection->getIterator());
	}
}