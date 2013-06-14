<?php

namespace Message\Mothership\CMS\Test\PageType;

use Message\Mothership\CMS\PageTypeInterface;

class Home implements PageTypeInterface
{
	public function getName()
	{
		return 'home';
	}

	public function getDescription()
	{
		return 'The home page!';
	}

	public function getFields()
	{
		// crazy shiz happens here.
	}
}