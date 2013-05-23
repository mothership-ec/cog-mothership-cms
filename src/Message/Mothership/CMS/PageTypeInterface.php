<?php

namespace Message\Mothership\CMS;

/**
 * Interface that must be used by all page type objects.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface PageTypeInterface
{
	/**
	 * Get the name of this page type.
	 *
	 * @return string The page type name
	 */
	public function getName();

	/**
	 * Get a description for this page type.
	 *
	 * @return string The page type description
	 */
	public function getDescription();


	public function getFields();
}