<?php

namespace Message\Mothership\CMS\Test\PageType;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Mothership\CMS\Field\Factory as FieldFactory;

class Home implements PageTypeInterface
{
	public function getName()
	{
		return 'home';
	}

	public function getDisplayName()
	{
		return 'Home';
	}

	public function getDescription()
	{
		return 'The home page!';
	}

	public function allowChildren()
	{
		return false;
	}

	public function getViewReference()
	{

	}

	public function setFields(FieldFactory $factory)
	{

	}
}