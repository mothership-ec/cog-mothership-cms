<?php

namespace Message\Mothership\CMS\Blog;

use Message\Mothership\CMS\Page\Content;
use Message\User\Group\Collection as UserGroups;
use Message\User;

class CommentPermissionResolver
{
	private $_validator;
	private $_userGroups;

	public function __construct(ContentValidator $validator, UserGroups $userGroups)
	{
		$this->_validator  = $validator;
		$this->_userGroups = $userGroups;
	}

	public function isVisible(Content $content, User\UserInterface $user)
	{
		try {
			$this->_validator->validate($content);
		} catch (InvalidContentException $e) {
			return false;
		}

		if ($this->_commentsDisabled($content)) {
			return false;
		}

		return $this->_userAllowed($content, $user);
	}
}