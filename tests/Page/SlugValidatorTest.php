<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Cog\ValueObject\Slug;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\SlugValidator;

class SlugValidatorTest extends \PHPUnit_Framework_TestCase
{
	public $validator;
	public $loader;

	public function setUp()
	{
		$this->loader    = $this->getMock('Message\Mothership\CMS\Page\Loader', [], [], '', false);
		$this->validator = new SlugValidator($this->loader);
	}

	public function testUniqueSlug()
	{
		$slug = new Slug('a-unique-slug');

		$this->validator->validate($slug);
	}

	/**
	 * @expectedException Message\Mothership\CMS\Exception\SlugExistsException
	 */
	public function testSlugExists()
	{
		$slug = new Slug('an-existing-slug');

		$this->loader->expects($this->any())
		             ->method('getBySlug')
		             ->with($slug)
		             ->will($this->returnValue(new Page));

		$this->validator->validate($slug);
	}

	/**
	 * @expectedException Message\Mothership\CMS\Exception\HistoricalSlugExistsException
	 */
	public function testSlugExistsHistorically()
	{

	}

	/**
	 * @expectedException Exception\DeletedSlugExistsException
	 */
	public function testSlugExistsOnDeletedPage()
	{

	}
}