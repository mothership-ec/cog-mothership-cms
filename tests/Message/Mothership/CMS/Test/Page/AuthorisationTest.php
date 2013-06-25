<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\Authorisation;
use Message\Mothership\CMS\Page\Page;
use Message\User\User;
use Message\User\Group\Loader;
use Message\User\AnonymousUser;
use Message\Mothership\CMS\Test\Page\GroupTest as Group;

class AuthorisationTest extends \PHPUnit_Framework_TestCase
{
	protected $_page;
	protected $_groupLoader;
	protected $_user;

	public function setUp()
	{
		$this->_user = new User;
		$this->_page = new Page;

		$this->_groupLoader = $this->getMockBuilder('Message\User\Group\Loader')
				     			   ->disableOriginalConstructor()
				     			   ->getMock();

		$this->_AuthPage = new Authorisation($this->_groupLoader, $this->_user);
	}

	/**
	 * @expectedException 'This page has no password'
	 */
	public function testValidatePasswordWithNoPassword()
	{

        try{
        	// Test no password given
        	
        	$this->_AuthPage;

        } catch(Exception $expected) {
        	return;
        }

        // $this->fail('Huh?');
	}

	public function testIsCurrentUserObjectLoadedDefault()
	{
		$this->assertTrue($this->_AuthPage->isViewable($this->_page));
	}

	public function testIsViewableAll()
	{

		$this->_page->access = Authorisation::ACCESS_ALL;

		$this->assertTrue($this->_AuthPage->isViewable($this->_page));
	}

	public function testIsViewableAllAnonymous()
	{
		$this->_page->access = Authorisation::ACCESS_ALL;

		$this->assertTrue($this->_AuthPage->isViewable($this->_page, new AnonymousUser));
	}

	public function testIsViewableGuest()
	{
		$this->_page->access = Authorisation::ACCESS_GUEST;

		$this->assertTrue($this->_AuthPage->isViewable($this->_page, new AnonymousUser));
	}

	public function testIsNotViewableGuest()
	{
		$this->_page->access = Authorisation::ACCESS_USER;

		$this->assertFalse($this->_AuthPage->isViewable($this->_page, new AnonymousUser));
	}

	public function testIsViewableLoggedIn()
	{
		$this->_page->access = Authorisation::ACCESS_USER;

		$this->assertTrue($this->_AuthPage->isViewable($this->_page, $this->_user));
	}

	public function testIsViewableSpecificUserGroup()
	{
		$this->_page->accessGroups = array('something', 'something else');

		$this->_page->access = Authorisation::ACCESS_USER_GROUP;

		$this->assertTrue($this->_AuthPage->isViewable($this->_page));
	}

	public function testIsNotViewableSpecificUserGroup()
	{
		
	}

	public function testIsPublished()
	{

	}
}