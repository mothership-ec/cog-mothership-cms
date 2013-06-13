<?php

namespace Message\Mothership\CMS\Field;

/**
 * Represents a page content field.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class Field implements FieldInterface
{
	protected $_name;
	protected $_label;
	protected $_localisable = false;
	protected $_value;

	/**
	 * {@inheritDoc}
	 */
	public function __construct($name, $label = null)
	{
		$this->_name  = $name;
		$this->_label = $label ?: $name;
	}

	/**
	 * Print the class directly. This returns the field value.
	 *
	 * @return string|null The field value
	 */
	public function __toString()
	{
		return $this->getValue();
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->_name;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getLabel()
	{
		return $this->_label;
	}

	/**
	 * Get the value for this field.
	 *
	 * @return mixed
	 */
	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * Checks if this field is localisable.
	 *
	 * @return boolean True if this field is localisable, false otherwise
	 */
	public function isLocalisable()
	{
		return $this->_localisable;
	}

	/**
	 * Set the value for this field.
	 *
	 * @param mixed $value The field value
	 */
	public function setValue($value)
	{
		$this->_value = $value;

		return $this;
	}

	/**
	 * Toggle whether this field is localisable.
	 *
	 * @param boolean $localisable Whether the field should be localisable
	 */
	public function setLocalisable($localisable = true)
	{
		$this->_localisable = (bool) $localisable;

		return $this;
	}

	/**
	 * Get the form field to use when rendering this field in a form.
	 *
	 * @todo set the return docblock here when we know the form field class hint
	 * @return ?
	 */
	abstract public function getFormField();
}