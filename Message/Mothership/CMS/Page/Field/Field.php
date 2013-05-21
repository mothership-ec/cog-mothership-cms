<?php

namespace Message\Mothership\CMS\Field;

/**
 * Represents a simple field of a page.
 *
 * A field may be "split", which means it is made up of different properties
 * that can be accessed independently. This is useful for attaching metadata to
 * a field or for special field types that have a few peices of data that need
 * indexing (for example, a product selector might have a product and colour ID).
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Field
{
	protected $_value;
	protected $_values;

	/**
	 * Constructor.
	 *
	 * @param mixed $value Field value, or array of value properties where the
	 *                     keys are the property names
	 */
	public function __construct($value)
	{
		// If the value is an array, it's a "split" field with multiple properties
		if (is_array($value)) {
			$this->_values = $value;
			$this->_value  = implode(':', $value);
		}

		$this->_value = $value;
	}

	/**
	 * Print the class directly. This returns the field value.
	 *
	 * If the field value was split into properties, this returns the value of
	 * all of the properties joined with colon characters. For example:
	 *
	 * <code>
	 * $field = new Field(array('productID' => 4, 'colourID' => 10));
	 * echo $field; // returns 4:5
	 * </code>
	 *
	 * @return string The field value
	 */
	public function __toString()
	{
		return $this->_value;
	}

	/**
	 * Get a field property.
	 *
	 * @param  string $name Property name
	 *
	 * @return mixed        The value of the field property
	 *
	 * @throws \OutOfBoundsException If the field property does not exist
	 */
	public function __get($name)
	{
		if (isset($this->_values[$name])) {
			return $this->_values[$name];
		}

		throw new \OutOfBoundsException(sprintf('Field property does not exist: `%s`', $name));
	}
}