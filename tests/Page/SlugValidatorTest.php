<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\SlugValidator;

class SlugValidatorTest extends \PHPUnit_Framework_TestCase
{
	public function setUp()
	{
		$this->_validator = new SlugValidator;
	}

	public function testUniqueSlug()
	{
		$slug = "a-unique-slug";

		$this->_validator->validate($slug);
	}

	/**
	 * @expectedException Exception\SlugExistsException
	 */
	public function testSlugExists()
	{

	}

	/**
	 * @expectedException Exception\HistoricalSlugExistsException
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