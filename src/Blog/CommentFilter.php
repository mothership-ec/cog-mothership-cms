<?php

namespace Message\Mothership\CMS\Blog;

use Message\User;

class CommentFilter
{
	public function __construct(User\UserInterface $user)
	{
		$this->_user = $user;
	}

	public function getAvailable(CommentCollection $comments)
	{
		$filtered = [];

		foreach ($comments as $comment) {
			if ($comment->isApproved() || ($this->_user instanceof User\User && $comment->isPendingAndByCurrentUser($this->_user->id))) {
				$filtered[] = $comment;
			}
		}

		return new CommentCollection($filtered);
	}
}