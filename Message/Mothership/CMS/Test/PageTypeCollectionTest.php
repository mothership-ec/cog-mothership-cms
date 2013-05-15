<?php

namespace Message\Mothership\CMS\Test;

use Message\Mothership\CMS\PageTypeCollection;

class PageTypeCollectionTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructorAndIteration()
	{
		$pageTypes = array(
			new PageType\Blog,
			new PageType\Blog,
			new PageType\Blog,
			new PageType\Blog,
			new PageType\Blog,
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
		$pageType   = new PageType\Blog;

		$this->assertEquals($collection, $collection->add($pageType));

		foreach ($collection as $name => $type) {
			$this->assertEquals($pageType->getName(), $name);
			$this->assertEquals($pageType, $type);
		}
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