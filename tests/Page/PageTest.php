<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\Page;

class PageTest extends \PHPUnit_Framework_TestCase
{
	protected $_page;

	public function setUp()
	{
		$this->_page = new Page;
	}

	public function testGetType()
	{
		// TODO: create blog page type fixture class to use here.
		$this->_page->type = 'blog';

		#$this->assertInstanceOf('Message\Mothership\CMS\PageType\\PageTypeInterface', $this->_page->getType());
	}
}