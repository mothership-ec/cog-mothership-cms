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

		// Expect the check for an existing slug
		$this->loader->expects($this->once())
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
		$slug = new Slug('an-historical-slug');

		// Ignore the check for existing slugs
		$this->loader->expects($this->once())
		             ->method('getBySlug')
		             ->will($this->returnValue(false));

		// Expect the check for a historical slug
		$this->loader->expects($this->once())
		             ->method('checkSlugHistory')
		             ->will($this->returnValue(new Page));

		$this->validator->validate($slug);
	}

	/**
	 * @expectedException Message\Mothership\CMS\Exception\DeletedSlugExistsException
	 */
	public function testSlugExistsOnDeletedPage()
	{
		$slug = new Slug('a-deleted-slug');

		// Ignore the check for existing slugs
		$this->loader->expects($this->at(0))
		             ->method('getBySlug')
		             ->will($this->returnValue(false));

		// Ignore the check for historical slugs
		$this->loader->expects($this->once())
		             ->method('checkSlugHistory')
		             ->will($this->returnValue(false));

		// Expect the check for a slug on a deleted page as the 4th function
		// call (3rd index)
		$this->loader->expects($this->at(3))
		             ->method('getBySlug')
		             ->will($this->returnValue(new Page));

		$this->validator->validate($slug);
	}
}