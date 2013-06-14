<?php

namespace Message\Mothership\CMS\Test\PageType;

use Message\Mothership\CMS\PageType\Collection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructorAndIteration()
	{
		$pageTypes = array(
			'blog' => new Blog,
			'home' => new Home,
		);
		$collection = new Collection($pageTypes);

		$this->assertEquals(count($pageTypes), $collection->count());

		foreach ($collection as $pageType) {
			$this->assertEquals($pageTypes[$pageType->getName()], $pageType);
		}
	}

	public function testAdd()
	{
		$collection = new Collection;
		$pageType   = new Blog;

		$this->assertEquals($collection, $collection->add($pageType));

		foreach ($collection as $name => $type) {
			$this->assertEquals($pageType, $type);
		}
	}

	public function testCount()
	{
		$collection = new Collection;

		$this->assertEquals(0, count($collection));
		$this->assertEquals(0, $collection->count());

		$collection->add(new Blog);

		$this->assertEquals(1, count($collection));
		$this->assertEquals(1, $collection->count());

		$collection->add(new Home);

		$this->assertEquals(2, count($collection));
		$this->assertEquals(2, $collection->count());
	}

	public function testGetIterator()
	{
		$collection = new Collection;
		$this->assertInstanceOf('\Iterator', $collection->getIterator());
	}
}