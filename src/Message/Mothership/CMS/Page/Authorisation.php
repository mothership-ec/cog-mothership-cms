<?php

namespace Message\Mothership\CMS\Page;

use Message\User\UserInterface;
use Message\User\AnonymousUser;
use Message\User\Group\Loader as GroupLoader;

/**
 * Helper class for determining if a page is viewable by the current user based
 * on the various authorisation and access settings.
 *
 * @author Danny Hannah <danny@message.co.uk>
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Authorisation
{
	const ACCESS_ALL        = 0;
	const ACCESS_GUEST      = 100;
	const ACCESS_USER       = 200;
	const ACCESS_USER_GROUP = 300;

	protected $_groupLoader;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param GroupLoader   $groupLoader The user group loader
	 * @param UserInterface $user        The currently logged in user
	 */
	public function __construct(GroupLoader $groupLoader, UserInterface $user)
	{
		$this->_groupLoader = $groupLoader;
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
	 * Check whether the given page is viewable by the given user.
	 *
	 * @param Page      $page The page to check
	 * @param User|null $user The user to check authorisation for, or if null
	 *                        the current user is used
	 *
	 * @return bool           Result of the check
	 *
	 * @throws \RuntimeException If the access level on the page was not understood
	 */
	public function isViewable(Page $page, UserInterface $user = null)
	{
		$user = $user ?: $this->_currentUser;

		switch ($page->access) {
			// If this page is accessible to anybody, it is always viewable
			case self::ACCESS_ALL:
				return true;
				break;
			// If this page is accessible to guests, check the user is not logged in
			case self::ACCESS_GUEST:
				return ($user instanceof AnonymousUser);
				break;
			// If this page is accessible to logged in users, check the user is logged in
			case self::ACCESS_USER:
				return ($user instanceof AnonymousUser);
				break;
			// If this page is accessible to users in specific groups, check the user's groups
			case self::ACCESS_USER_GROUP:
				$userGroups = $this->_groupLoader->getByUser($user);
				foreach ($page->accessGroups as $pageGroup) {
					foreach ($userGroups as $userGroup) {
						if ($userGroup->getName() === $pageGroup->getName()) {
							return true;
						}
					}
				}

				return false;
				break;
			default:
				throw new \RuntimeException(sprintf('Invalid access level `%s` on page', $page->access));
				break;
		}

		return false;
	}

	/**
	 * Check that the given page is published.
	 *
	 * @param Page $page The page to check
	 *
	 * @return bool      Result of the check
	 */
	public function isPublished(Page $page)
	{
		return $page->publishDateRange->isInRange();
	}
}