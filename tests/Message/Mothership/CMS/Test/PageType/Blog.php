<?php

namespace Message\Mothership\CMS\Test\PageType;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Mothership\CMS\Field\Factory as FieldFactory;

class Blog implements PageTypeInterface
{
	public function getName()
	{
		return 'blog';
	}

	public function getDisplayName()
	{
		return 'Blog';
	}

	public function getDescription()
	{
		return 'A blog page type, for use when unit testing.';
	}

	public function allowChildren()
	{
		return false;
	}

	public function getViewReference()
	{
		'::View:PageTypes:Blog';
	}

	public function setFields(FieldFactory $factory)
	{

	}
}