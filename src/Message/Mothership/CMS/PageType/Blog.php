<?php

namespace Message\Mothership\CMS\PageType;

/**
 * Interface that must be used by all page type objects.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Blog implements PageTypeInterface
{
	/**
	 * Get the name of this page type.
	 *
	 * @return string The page type name
	 */
	public function getName()
	{
		return 'blog';
	}

	/**
	 * Get a description for this page type.
	 *
	 * @return string The page type description
	 */
	public function getDescription()
	{
		return 'this is a derscription';
	}


	public function getFields()
	{
		return array();
	}
}