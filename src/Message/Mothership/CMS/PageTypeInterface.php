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
	 * Get the identifying name of this page type.
	 *
	 * These must be unique: if there is more than one page type registered with
	 * the same name, an error will be thrown.
	 *
	 * @return string The page type name
	 */
	public function getName();

	/**
	 * Get a nicely formatted name for this page type that can be displayed to
	 * the user.
	 *
	 * @return string The page type name
	 */
	public function getDisplayName();

	/**
	 * Get a description for this page type.
	 *
	 * @return string The page type description
	 */
	public function getDescription();

	/**
	 * Check if this page type allows children pages.
	 *
	 * @return boolean True if child pages are allowed, false otherwise
	 */
	public function allowChildren();


	public function getFields();
		// array of Field and Group instances
		// nice api
		// validation stuff set in here, not in the field objects
}