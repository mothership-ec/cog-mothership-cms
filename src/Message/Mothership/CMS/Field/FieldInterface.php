<?php

namespace Message\Mothership\CMS\Field;

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
	public function __construct($name, $label = null);

	public function getName();

	public function getLabel();
}