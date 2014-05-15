<?php

namespace Message\Mothership\CMS\PageType;

use Message\Cog\Field\Factory;
use Message\Cog\Field\Group;
use Message\Cog\Field\ContentTypeInterface;

/**
 * Interface that must be used by all page type objects.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface PageTypeInterface extends ContentTypeInterface
{
	/**
	 * Check if this page type allows children pages.
	 *
	 * @return boolean True if child pages are allowed, false otherwise
	 */
	public function allowChildren();

	/**
	 * Get a cog reference to the view file to use when rendering pages of this
	 * type.
	 *
	 * @return string The cog reference
	 */
	public function getViewReference();
}
