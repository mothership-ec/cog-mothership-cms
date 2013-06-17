<?php

namespace Message\Mothership\CMS\Page;

use Message\User\UserInterface;

/**
 * Helper class for determining if a page is viewable by the current user based
 * on the various authorisation and access settings.
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class Authorisation
{
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param UserInterface $user The currently logged in user
	 */
	public function __construct(UserInterface $user)
	{
		$this->_currentUser = $user;
	}

	/**
	 * Check that a given string matches the stored password for the page.
	 *
	 * @param  Page  $page     The page to check
	 * @param  mixed $password The password to check for
	 * @return bool  		   Result of the check
	 *
	 * @throws \Exception      If the given page has no password set
	 */
	public function validatePassword(Page $page, $password)
	{
		if (!$page->password) {
			throw new \Exception('This page has no password');
		}

		return $page->password == $password;
	}

	/**
	 * Check whether the given page is viewable by the given user
	 *
	 * @param Page      $page The page to check
	 * @param User|null $user The user to check authorisation for, or if null
	 *                        the current user is used
	 *
	 * @return bool           Result of the check
	 */
	public function isViewable(Page $page, UserInterface $user = null)
	{
		$user = $user ?: $this->_currentUser;


	}

	/**
	 * Check that the given page is published.
	 *
	 * @return bool Result of the check
	 */
	public function isPublished()
	{
		return $this->_page->publishDateRange->isInRange();
	}
}