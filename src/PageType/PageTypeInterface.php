<?php

namespace Message\Mothership\CMS\PageType;

use Message\Cog\Field\Factory;
use Message\Cog\Field\Group;

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

	/**
	 * Get a cog reference to the view file to use when rendering pages of this
	 * type.
	 *
	 * @return string The cog reference
	 */
	public function getViewReference();

	/**
	 * Set the content fields & groups for this page type on a field factory
	 * instance.
	 *
	 * @param  Factory $factory The field factory to use
	 */
	public function setFields(Factory $factory);
}