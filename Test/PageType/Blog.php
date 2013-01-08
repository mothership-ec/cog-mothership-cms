<?php

namespace Message\CMS\Test\PageType;

use Message\CMS\PageTypeInterface;

class Blog implements PageTypeInterface
{
	public function getDescription()
	{
		return 'A blog page type, for use when unit testing.';
	}

	public function getFields()
	{
		// crazy shiz happens here.
	}
}