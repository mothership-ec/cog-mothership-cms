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

		$this->assertSame(count($pageTypes), $collection->count());

		foreach ($collection as $pageType) {
			$this->assertSame($pageTypes[$pageType->getName()], $pageType);
		}
	}

	public function testAddAndGet()
	{
		$collection = new Collection;
		$pageType   = new Blog;

		$this->assertSame($collection, $collection->add($pageType));

		$this->assertSame($pageType, $collection->get($pageType->getName()));

		return $collection;
	}

	/**
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage already defined
	 */
	public function testAddingPageTypeTwice()
	{
		$collection = new Collection;

		$collection
			->add(new Blog)
			->add(new Blog);
	}

	/**
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage not set
	 */
	public function testGetUnknownPageType()
	{
		$collection = new Collection;

		$collection->get('doesnotexist');
	}

	public function testCount()
	{
		$collection = new Collection;

		$this->assertSame(0, count($collection));
		$this->assertSame(0, $collection->count());

		$collection->add(new Blog);

		$this->assertSame(1, count($collection));
		$this->assertSame(1, $collection->count());

		$collection->add(new Home);

		$this->assertSame(2, count($collection));
		$this->assertSame(2, $collection->count());
	}

	public function testGetIterator()
	{
		$collection = new Collection;
		$this->assertInstanceOf('\Iterator', $collection->getIterator());
	}
}