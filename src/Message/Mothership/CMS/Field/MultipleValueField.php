<?php

namespace Message\Mothership\CMS\Field;

/**
 * Represents a page content field that has multiple, separate values.
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

	/**
	 * Set an array of the values
	 *
	 * @param array $values [description]
	 */
	public function setValues(array $values)
	{
		foreach ($values as $name => $value) {
			$this->add($name, $value);
		}
	}

	/**
	 * Add a value to this field.
	 *
	 * @param string $key   The field key
	 * @param mixed  $value The field value
	 *
	 * @throws \InvalidArgumentException If the value key is falsey
	 * @throws \InvalidArgumentException If the value key is not valid (does not
	 *                                   exist in self::getValueKeys())
	 */
	public function setValue($key, $value)
	{
		if (!$key) {
			throw new \InvalidArgumentException('Field value must have a key');
		}

		if (!in_array($key, $this->getValueKeys())) {
			throw new \InvalidArgumentException(sprintf(
				'Value key `%s` invalid. Allowed names: `%s`',
				$key,
				implode('`, `', $this->getValueKeys())
			);
		}

		$this->_value[$key] = $value;
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

	/**
	 * Get the keys allowed for values in this field.
	 *
	 * @return array An array of allowed keys
	 */
	abstract public function getValueKeys();
}