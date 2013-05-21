<?php

namespace Message\Mothership\CMS\Field;

class Field
{
	protected $_value;
	protected $_values;

	public function __construct($value)
	{
		// If the value is an array, it's a "split" field with multiple parts
		if (is_array($value)) {
			$this->_values = $value;
			$this->_value  = implode(':', $value);
		}

		$this->_value = $value;
	}

	public function __toString()
	{
		return $this->_value;
	}

	public function __get($name)
	{
		if (isset($this->_values[$name])) {
			return $this->_values[$name];
		}

		throw new \OutOfBoundsException(sprintf('Field property does not exist: `%s`', $name));
	}
}