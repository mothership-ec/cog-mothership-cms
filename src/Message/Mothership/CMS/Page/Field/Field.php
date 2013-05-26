<?php

namespace Message\Mothership\CMS\Page\Field;

/**
 * Represents a simple field of a page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Field
{
	protected $_value;

	/**
	 * Constructor.
	 *
	 * @param mixed $value The field's value
	 */
	public function __construct($value)
	{
		$this->_value = $value;
	}

	/**
	 * Print the class directly. This returns the field value.
	 *
	 * @return string The field value
	 */
	public function __toString()
	{
		return $this->_value;
	}
	// extend this class for each field type
	// name
	// localisable
	// type
	public function getFormField()
	{
		// return a Symfony field object
		// with validation information set
		// contextual help
	}
}