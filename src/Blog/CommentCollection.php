<?php

namespace Message\Mothership\CMS\Blog;

use Message\Cog\ValueObject\Collection;

/**
 * Class CommentCollection
 * @package Message\Mothership\CMS\Blog
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class CommentCollection extends Collection
{
	protected function _configure()
	{
		$this->addValidator(function($item) {
			if (!$item instanceof Comment) {
				throw new \InvalidArgumentException('Comments in collection must be an instance of ' . __NAMESPACE__ . '\\Comment');
			}
		});

		$this->setKey(function($item) {
			return $item->getID();
		});
	}
}