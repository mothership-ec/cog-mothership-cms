<?php

namespace Message\Mothership\CMS\Blog;

use Message\Cog\ValueObject\Collection;

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