<?php

namespace Message\Mothership\CMS\Page;

/**
 * Helper class for determining if a page is viewable by the current user based
 * on the various authorisation and access settings.
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class Authorisation
{
	/**
	 * Get an instance of the given page along with an instance of the User
	 *
	 * @param Page $page
	 * @param User $user
	 */
	public function __construct(Page $page, User $user)
	{

	}

	/**
	 * Check that a given string matches the stored password for the page.
	 *
	 * @param  mixed $password The password to check for
	 * @return bool  		   Result of the check
	 *
	 * @throws \Exception      If the given page has no password set
	 */
	public function validatePassword($password)
	{

	}

	/**
	 * Check whether the given page is viewable by the given user
	 *
	 * @return bool			   Result of the check
	 */
	public function isViewable()
	{

	}

	/**
	 * Check that the given page is ready to be viewed and not in a draft or
	 * hidden state.
	 *
	 * @return bool			   Result of the check
	 */
	public function isPublished()
	{

	}
}