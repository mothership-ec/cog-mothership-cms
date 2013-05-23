<?php

namespace Message\Mothership\CMS\Test\PageType;

use Message\Mothership\CMS\PageTypeInterface;

class Blog implements PageTypeInterface
{
	public function getName()
	{
		return 'blog';
	}

	public function getDescription()
	{
		return 'A blog page type, for use when unit testing.';
	}

	public function getFields()
	{
		// crazy shiz happens here.
	}
}