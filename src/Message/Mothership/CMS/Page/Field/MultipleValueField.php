<?php

namespace Message\Mothership\CMS\Page\Field;

/**
 * Represents a field of a page that has multiple, separate values.
 *
 * This is useful for attaching metadata to a field or for special field types
 * that have a few peices of data that need indexing and storing separately (for
 * example, a product selector might have a product and colour ID).
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class MultipleValueField extends Field
{
	protected $_value = array();

	/**
	 * @see setValue()
	 */
	public function __set($name, $value)
	{
		$this->setValue($name, $value);
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
		if (isset($this->_value[$name])) {
			return $this->_value[$name];
		}

		throw new \OutOfBoundsException(sprintf('Field value does not exist: `%s`', $name));
	}

	/**
	 * Check if a value is set on this field.
	 *
	 * @param  string  $name Value name
	 *
	 * @return boolean       True if the value is set
	 */
	public function __isset($name)
	{
		return isset($this->_value[$name]);
	}

	public function setValues(array $values)
	{
		foreach ($values as $name => $value) {
			$this->add($name, $value);
		}
	}

	/**
	 * Add a value to this field.
	 *
	 * @param string $name  The field name
	 * @param mixed  $value The field value
	 *
	 * @throws \InvalidArgumentException If the field name is falsey
	 */
	public function setValue($name, $value)
	{
		if (!$name) {
			throw new \InvalidArgumentException('Page field value must have a name');
		}

		$this->_value[$name] = $value;
	}

	/**
	 * Print the class directly. This returns all of the field values
	 * concatenated with a colon character.
	 *
	 * @return string The field values as a string
	 */
	public function getValue()
	{
		return implode(':', $this->_value);
	}
}