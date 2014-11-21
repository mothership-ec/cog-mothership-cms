<?php

namespace Message\Mothership\CMS\Blog;

use Message\User;

/**
 * Class CommentFilter
 * @package Message\Mothership\CMS\Blog
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Class for filtering out comments that the user should not see
 */
class CommentFilter
{
	public function __construct(User\UserInterface $user)
	{
		$this->_user = $user;
	}

	/**
	 * Get all comments from collection that are available for the user to see
	 *
	 * @param CommentCollection $comments
	 *
	 * @return CommentCollection
	 */
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