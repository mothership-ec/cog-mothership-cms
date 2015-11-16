<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Test\PageType\Blog;

class PageTest extends \PHPUnit_Framework_TestCase
{
	protected $_page;

	public function setUp()
	{
		$this->_page = new Page;
	}

	public function testGetType()
	{
		$this->_page->type = new Blog;

		$this->assertInstanceOf('Message\Mothership\CMS\PageType\\PageTypeInterface', $this->_page->getType());
	}
}