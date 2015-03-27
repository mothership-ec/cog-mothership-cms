<?php

namespace Message\Mothership\CMS\Blog;

use Message\Mothership\CMS\Page\Content;

use Message\Mothership\User\Avatar;

use Message\User\UserInterface;
use Message\User\AnonymousUser;
use Message\User\User;
use Message\User\Group\Loader as UserGroupLoader;

use Message\Cog\HTTP\Request;

/**
 * Class CommentBuilder
 * @package Message\Mothership\CMS\Blog
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Class for building instances of Comment from data
 */
class CommentBuilder
{
	// Form data constants
	const NAME    = 'name';
	const EMAIL   = 'email';
	const WEBSITE = 'website';
	const COMMENT = 'comment';

	/**
	 * @var \Message\User\UserInterface
	 */
	private $_user;

	/**
	 * @var \Message\Cog\HTTP\Request
	 */
	private $_request;

	private $_contentValidator;

	private $_userGroupLoader;

	/**
	 * @param UserInterface $user
	 * @param Request $request
	 * @param ContentValidator $contentValidator
	 */
	public function __construct(UserInterface $user, Request $request, ContentValidator $contentValidator, UserGroupLoader $userGroupLoader)
	{
		$this->_user             = $user;
		$this->_request          = $request;
		$this->_contentValidator = $contentValidator;
		$this->_userGroupLoader  = $userGroupLoader;
	}

	/**
	 * Method for creating an instance of Comment using data taken from the comment form
	 *
	 * @param int $pageID
	 * @param array $data
	 * @param Content $content
	 *
	 * @return Comment
	 */
	public function buildFromForm($pageID, array $data, Content $content)
	{
		$this->_validateData($data);
		$this->_contentValidator->validate($content);
		$comment = new Comment;

		$comment->setPageID($pageID);
		$comment->setName($data[self::NAME]);
		$comment->setEmail($data[self::EMAIL]);

		if (!empty($data[self::WEBSITE])) {
			$comment->setWebsite($data[self::WEBSITE]);
		}

		$comment->setContent($data[self::COMMENT]);
		$comment->setIpAddress($this->_request->getClientIp());

		if ($this->_user instanceof User) {
			$comment->setUserID($this->_user->id);
		}

		$this->_setStatus($comment, $content);

		return $comment;
	}

	/**
	 * Set the status of the comment, depending on the configuration of the blog post, and who is making the comment.
	 * A super admin's comment will always be approved right away
	 *
	 * @param Comment $comment
	 * @param Content $content
	 *
	 * @return Comment
	 */
	private function _setStatus(Comment $comment, Content $content)
	{
		if (!$this->_user instanceof AnonymousUser) {
			$userGroups = $this->_userGroupLoader->getByUser($this->_user);

			foreach ($userGroups as $group) {
				if ($group->getName() === 'ms-super-admin') {
					$comment->setStatus(Statuses::APPROVED);

					return $comment;
				}
			}
		}

		$allowed = $content->{ContentOptions::COMMENTS}->{ContentOptions::ALLOW_COMMENTS}->getValue();

		$comment->setStatus($allowed === ContentOptions::APPROVE ? Statuses::PENDING : Statuses::APPROVED);

		return $comment;
	}

	/**
	 * Validates the form data to ensure that the relevant fields exist in order to create the Comment object
	 *
	 * @param array $data
	 * @throws \LogicException
	 */
	private function _validateData(array $data)
	{
		$keys = [
			self::NAME,
			self::EMAIL,
			self::COMMENT
		];

		foreach ($keys as $key) {
			if (!array_key_exists($key, $data)) {
				throw new \LogicException('`' . $key . '` missing from data');
			}
		}
	}
}