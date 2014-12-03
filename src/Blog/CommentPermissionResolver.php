<?php

namespace Message\Mothership\CMS\Blog;

use Message\Mothership\CMS\Page\Content;
use Message\User\Group\Loader as GroupLoader;
use Message\User;

/**
 * Class CommentPermissionResolver
 * @package Message\Mothership\CMS\Blog
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Class to determine whether or not a user is able to submit a comment on a blog post.
 */
class CommentPermissionResolver
{
	/**
	 * @var ContentValidator
	 */
	private $_validator;

	/**
	 * @var \Message\User\Group\Loader
	 */
	private $_groupLoader;

	public function __construct(ContentValidator $validator, GroupLoader $groupLoader)
	{
		$this->_validator   = $validator;
		$this->_groupLoader = $groupLoader;
	}

	/**
	 * Determine whether or not a comment form is visible on a page
	 *
	 * @param Content $content
	 * @param User\UserInterface $user
	 *
	 * @return bool
	 */
	public function isVisible(Content $content, User\UserInterface $user)
	{
		try {
			$this->_validator->validate($content);
		} catch (InvalidContentException $e) {
			return false;
		}

		if ($this->commentsDisabled($content)) {
			return false;
		}

		return $this->userAllowed($content, $user);
	}

	/**
	 * Determine whether or not comments are disabled
	 *
	 * @param Content $content
	 *
	 * @return bool
	 */
	public function commentsDisabled(Content $content)
	{
		return $content->{ContentOptions::COMMENTS}->{ContentOptions::ALLOW_COMMENTS}->getValue() === ContentOptions::DISABLED;
	}

	/**
	 * Determine whether or not the user has permission to submit a comment
	 *
	 * @param Content $content
	 * @param User\UserInterface $user
	 *
	 * @return bool
	 */
	public function userAllowed(Content $content, User\UserInterface $user)
	{
		$allowedGroups = $content->{ContentOptions::COMMENTS}->{ContentOptions::PERMISSION}->getValue();

		if ($user instanceof User\AnonymousUser) {
			return array_key_exists(ContentOptions::GUEST, $allowedGroups);
		}
		if (array_key_exists(ContentOptions::LOGGED_IN, $allowedGroups)) {
			return true;
		}

		$userGroups = $this->_groupLoader->getByUser($user);

		foreach ($userGroups as $userGroup) {
			if (array_key_exists($userGroup->getName(), $allowedGroups)) {
				return true;
			}
		}

		return false;
	}
}