<?php

namespace Message\Mothership\CMS\Field;

/**
 * Base field object that should be inherited by both a normal field and a
 * "multiple value" field.
 *
 * Note that it's important that `setValue()` is not defined here, because the
 * method signatures are different for each subclass.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class BaseField implements FieldInterface
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
	 * Checks if this field is localisable.
	 *
	 * @return boolean True if this field is localisable, false otherwise
	 */
	public function isLocalisable()
	{
		return $this->_localisable;
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
	 * Get the value for this field.
	 *
	 * @return mixed
	 */
	abstract public function getValue();

	/**
	 * Get the form field to use when rendering this field in a form.
	 *
	 * @todo set the return docblock here when we know the form field class hint
	 * @return ?
	 */
	abstract public function getFormField();
}