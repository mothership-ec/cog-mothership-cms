<?php

namespace Message\Mothership\CMS\Field;

use Message\Cog\Validation\Validator;

/**
 * Interface defining a page field or a group of page fields.
 *
 * This is handy for hinting when you don't know if you will get a base field
 * or a group of fields.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
interface FieldInterface
{
	/**
	 * Constructor.
	 *
	 * @param string $name  Identifier name for this field (unique to the page type)
	 * @param string $label An optional human-readable label for this field
	 */
	public function __construct(Validator $validator, $name, $label = null);

	/**
	 * Get the identifier name for this field.
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * Get the human-readable label for this field.
	 *
	 * @return string
	 */
	public function getLabel();

	/**
	 * Set the group that a field is within
	 *
	 * @param Group $group The group this field lives within
	 */
	public function setGroup(Group $group);

	public function setTranslationKey($key);
}