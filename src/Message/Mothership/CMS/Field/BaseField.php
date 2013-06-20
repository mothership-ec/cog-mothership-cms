<?php

namespace Message\Mothership\CMS\Field;

use Message\Cog\Validation\Validator;

/**
 * Base field object that should be inherited by both a normal field and a
 * "multiple value" field.
 *
 * Note that it's important that `setValue()` is not defined here, because the
 * method signatures are different for each subclass.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author James Moss <james@message.co.uk>
 */
abstract class BaseField implements FieldInterface
{
	protected $_name;
	protected $_label;
	protected $_localisable = false;
	protected $_value;
	protected $_validator;
	protected $_group;
	protected $_translationKey;

	/**
	 * {@inheritDoc}
	 */
	public function __construct(Validator $validator, $name, $label = null)
	{
		$this->_validator = $validator;
		$this->_name      = $name;
		$this->_label     = $label ?: $name;
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

	public function val()
	{
		return $this->_validator->field($this->getName(), $this->getLabel());
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
	 * {inheritDoc}
	 */
	public function setGroup(Group $group)
	{
		$this->_group = $group;
	}

	public function setTranslationKey($key)
	{
		$this->_translationKey = $key;
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
	abstract public function getFormField($form);
}