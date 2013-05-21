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
class MultipleValueField extends Field
{
	protected $_values;

	/**
	 * Constructor.
	 *
	 * @param mixed $value Array of field values
	 */
	public function __construct(array $values = array())
	{
		foreach ($values as $name => $value) {
			$this->add($name, $value);
		}
	}

	/**
	 * Print the class directly. This returns all of the field values
	 * concatenated with a colon character.
	 *
	 * @return string The field values as a string
	 */
	public function __toString()
	{
		return implode(':', $this->_values);
	}

	/**
	 * @see add()
	 */
	public function __set($name, $value)
	{
		$this->add($name, $value);
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
		return isset($this->_values[$name]);
	}

	/**
	 * Add a value to this field.
	 *
	 * @param string $name  The field name
	 * @param mixed  $value The field value
	 *
	 * @throws \InvalidArgumentException If the field name is falsey
	 */
	public function add($name, $value)
	{
		if (!$name) {
			throw new \InvalidArgumentException('Page field value must have a name');
		}

		$this->_values[$name] = $value;
	}
}