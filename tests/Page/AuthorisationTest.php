<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\Authorisation;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\User\User;

class AuthorisationTest extends \PHPUnit_Framework_TestCase
{
	protected $_page;

	public function setUp()
	{
		$this->markTestIncomplete('Fix me');
		$user = new User;
		$page = new Page;
		$this->_page = new Authorisation($page, $user);
	}

	public function testValidatePassword()
	{
		try {
			$this->_page->validatePassword();
		} catch (InvalidArgumentException $expected) {
			return;
		}

		$this->fail('An expected exception has not been raised.');
	}

	public function testIsViewable()
	{

	}

	public function testIsPublished()
	{

	}
}