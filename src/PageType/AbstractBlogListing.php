<?php

namespace Message\Mothership\CMS\PageType;

use Message\Cog\Field\Factory as FieldFactory;

use Symfony\Component\Validator\Constraints;

abstract class AbstractBlogListing implements PageTypeInterface
{
	public function getName()
	{
		return 'blog_listing';
	}

	public function getDisplayName()
	{
		return 'Blog listing';
	}

	public function getDescription()
	{
		return 'A blog listing page';
	}

	public function allowChildren()
	{
		return true;
	}

	public function setFields(FieldFactory $factory)
	{

	}
}