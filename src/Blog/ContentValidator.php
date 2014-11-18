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

		if (null === $comments->{ContentOptions::ALLOW_COMMENTS}) {
			throw new InvalidContentException('Option for `' . ContentOptions::ALLOW_COMMENTS . '` not defined');
		}

		$allowComments = $comments->{ContentOptions::ALLOW_COMMENTS};

		if (!$allowComments instanceof Field\Field) {
			throw new InvalidContentException('`' . ContentOptions::ALLOW_COMMENTS . '` must be an instance of Message\\Cog\\Field\\Field');
		}

		if ($allowComments->getValue() === ContentOptions::DISABLED) {
			throw new InvalidContentException('Comments are disabled for this page, creating this comment should not have been possible');
		}

		if ($allowComments->getValue() !== ContentOptions::ALLOW && $allowComments->getValue() !== ContentOptions::APPROVE) {
			throw new InvalidContentException('Comment setting `' . $allowComments->getValue() . '` is invalid');
		}
	}
}