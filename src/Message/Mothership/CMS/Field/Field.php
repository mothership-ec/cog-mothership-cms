<?php

namespace Message\Mothership\CMS\Field;

/**
 * Represents a simple field of a page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 *
 * @todo Represent contextual help somehow?
 */
abstract class Field
{
	protected $_name;
	protected $_label;
	protected $_localisable = false;
	protected $_value;

	/**
	 * Constructor.
	 *
	 * @param string $name  The field's name
	 * @param string $label The field's label. If blank, the name will be used
	 */
	public function __construct($name, $label = null)
	{
		$this->_name  = $name;
		$this->_label = $label ?: $name;
	}

	/**
	 * Print the class directly. This returns the field value.
	 *
	 * @return string The field value
	 */
	public function __toString()
	{
		return $this->getValue();
	}

	public function setValue($value)
	{
		$this->_value = $value;

		return $this;
	}

	public function setLocalisable($localisable = true)
	{
		$this->_localisable = (bool) $localisable;

		return $this;
	}

	public function getValue()
	{
		return $this->_value;
	}

	public function isLocalisable()
	{
		return $this->_localisable;
	}

	abstract public function getFormField();
}