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
	 * @param mixed $value The field's vaulue
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
}