<?php

namespace Message\Mothership\CMS\Blog;

use Message\Mothership\CMS\Page\Content;

use Message\Mothership\User\Avatar;

use Message\User\UserInterface;
use Message\User\User;

use Message\Cog\HTTP\Request;

/**
 * Class CommentBuilder
 * @package Message\Mothership\CMS\Blog
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

	/**
	 * @param UserInterface $user
	 * @param Request $request
	 * @param ContentValidator $contentValidator
	 */
	public function __construct(UserInterface $user, Request $request, ContentValidator $contentValidator)
	{
		$this->_user             = $user;
		$this->_request          = $request;
		$this->_contentValidator = $contentValidator;
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
		$allowed = $content->{ContentOptions::COMMENTS}->{ContentOptions::ALLOW_COMMENTS}->getValue();

		$comment->setStatus($allowed === ContentOptions::APPROVE ? Statuses::PENDING : Statuses::APPROVED);

		if ($this->_user instanceof User) {
			$comment->setUserID($this->_user->id);
		}

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