<?php

namespace Message\Mothership\CMS\Blog;

use Message\Mothership\CMS\Page\Content;

use Message\Cog\Field;

/**
 * Class ContentValidator
 * @package Message\Mothership\CMS\Blog
 *
 * Class for validating that the Content object on a page has all the necessary fields to process a comment
 */
class ContentValidator
{
	public function isValid(Content $content)
	{
		try {
			$this->validate($content);
		} catch (InvalidContentException $e) {
			return false;
		}

		return true;
	}

	public function validate(Content $content)
	{
		$comments = $content->{ContentOptions::COMMENTS};
		if (null === $comments) {
			throw new InvalidContentException('`comments` group not declared on Content object');
		}

		if (!$comments instanceof Field\Group) {
			throw new InvalidContentException(
				'`' . ContentOptions::COMMENTS . '` must be a content group, instance of ' .
				(gettype($comments) === 'object' ? get_class($comments) : gettype($comments)) .
				' given'
			);
		}

		$this->_validateDisplayOptions($comments);
		$this->_validateAccessOptions($comments);

	}

	private function _validateDisplayOptions(Field\Group $comments)
	{
		if (null === $comments->{ContentOptions::ALLOW_COMMENTS}) {
			throw new InvalidContentException('Option for `' . ContentOptions::ALLOW_COMMENTS . '` not defined');
		}

		$allowComments = $comments->{ContentOptions::ALLOW_COMMENTS};

		if (!$allowComments instanceof Field\Field) {
			throw new InvalidContentException('`' . ContentOptions::ALLOW_COMMENTS . '` must be an instance of Message\\Cog\\Field\\Field');
		}

		if ($allowComments->getValue() === ContentOptions::DISABLED) {
			throw new InvalidContentException('Comments are disabled for this page');
		}

		if ($allowComments->getValue() !== ContentOptions::ALLOW && $allowComments->getValue() !== ContentOptions::APPROVE) {
			throw new InvalidContentException('Comment setting `' . $allowComments->getValue() . '` is invalid');
		}
	}

	private function _validateAccessOptions(Field\Group $comments)
	{
		if (null === $comments->{ContentOptions::PERMISSION}) {
			throw new InvalidContentException('Option for `' . ContentOptions::PERMISSION . '` not set');
		}

		$permission = $comments->{ContentOptions::PERMISSION};

		if (!$permission instanceof Field\MultipleValueField) {
			throw new InvalidContentException('`' . ContentOptions::PERMISSION . '` field must be an instance of Message\\Cog\\Field\\MultipleValueField');
		}

		$permissionValue = $permission->getValue();

		if (empty($permissionValue)) {
			throw new InvalidContentException('`' . ContentOptions::PERMISSION . '` is not determined');
		}
	}
}