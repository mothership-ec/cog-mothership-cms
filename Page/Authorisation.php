<?php

namespace Message\CMS\Page;

/**
 * Authorisation class will handle basic authorisation on any CMS page if the 
 * given page has requested it
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class Authorisation
{

	/**
	 * get an instance of the given page along with an instance of the User
	 *
	 *
	 * @param Page $page
	 * @param User $user
	 *
	 * @access public
	 */
	public function __construct(Page $page, User $user)
	{

	}

	/**
	 * validatePassword function pass in a password to check the validation of the
	 * supplied password.
	 *
	 * @param mixed $password (default: null)
	 *
	 * @return bool will return true / false is the validation is correct
	 * @access public
	 */
	public function validatePassword($password = null)
	{

	}

	/**
	 * isViewable will check wether the given page is vieable by the given user
	 * for instance it should check that the user has he correct access level
	 * and that the page is published etc...
	 *
	 * @return bool
	 * @access public
	 */
	public function isViewable()
	{

	}

	/**
	 * isPublished will check that the given page is ready to be viewed and not
	 * in a draft or hidden state.
	 *
	 * @return bool
	 * @access public
	 */
	public function isPublished()
	{

	}
}