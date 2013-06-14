<?php

namespace Message\Mothership\CMS\Field;

/**
 * Represents a group of page content fields.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Group implements FieldInterface
{
	protected $_name;
	protected $_label;

	protected $_repeatable = false;
	protected $_repeatableMin;
	protected $_repeatableMax;

	protected $_fields = array();
	protected $_idFieldName;

	/**
	 * {@inheritDoc}
	 */
	public function __construct($name, $label = null)
	{
		$this->_name  = $name;
		$this->_label = $label ?: $name;
	}

	/**
	 * Get a field in this group.
	 *
	 * @param  string $name Field name
	 *
	 * @return mixed        The field
	 *
	 * @throws \OutOfBoundsException If the field does not exist
	 */
	public function __get($name)
	{
		if (isset($this->_fields[$name])) {
			return $this->_fields[$name];
		}

		throw new \OutOfBoundsException(sprintf('Group field does not exist: `%s`', $name));
	}

	/**
	 * Check if a field exists in this group
	 *
	 * @param  string  $name Field name
	 *
	 * @return boolean       True if the field exists on this group
	 */
	public function __isset($name)
	{
		return isset($this->_fields[$name]);
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
	 * Check if this group is a repeatable group.
	 *
	 * @return boolean
	 */
	public function isRepeatable()
	{
		return $this->_repeatable;
	}

	/**
	 * Get the field set as the "identifier field".
	 *
	 * @return Field|false The field instance, or false if no identifier field set
	 */
	public function getIdentifierField()
	{
		return $this->_idFieldName ? $this->_fields[$this->_idFieldName] : false;
	}

	/**
	 * Add a field to this group.
	 *
	 * If the field has one of the following names and an "identifier field"
	 * has not yet been set on this group, the field will be used as the
	 * identifier:
	 *
	 *  - id
	 *  - identifier
	 *  - title
	 *  - heading
	 *
	 * @param Field  $field The field to add
	 *
	 * @return Group        Returns $this for chainability
	 */
	public function add(Field $field)
	{
		$this->_fields[$field->getName()] = $field;

		// If no identifier field is set yet and this field is a good candidate, set it
		if (!$this->getIdentifierField() && in_array($field->getName(), array(
			'id',
			'identifier',
			'title',
			'heading',
			'name',
		))) {
			$this->setIdentifierField($field->getName());
		}

		return $this;
	}

	/**
	 * Set repeatable information for this group.
	 *
	 * As well as being repeatable, a group can have a minimum and maximum
	 * number of repeats.
	 *
	 * @param boolean   $repeatable True to make this group repeatable, false
	 *                              otherwise
	 * @param int|null  $min        Minimum number of times this group can be
	 *                              repeated
	 * @param int|null  $max        Maximum number of times this group can be
	 *                              repeated
	 *
	 * @return Group                Returns $this for chainability
	 */
	public function setRepeatable($repeatable = true, $min = null, $max = null)
	{
		$this->_repeatable = (bool) $repeatable;

		if ($min) {
			$this->_repeatableMin = (int) $min;
		}

		if ($max) {
			$this->_repeatableMax = (int) $max;
		}

		return $this;
	}

	/**
	 * Set the field to use as a "identifier field".
	 *
	 * @param string $fieldName Name of the field to use
	 *
	 * @throws \InvalidArgumentException If a field with this name doesn't exist
	 *                                   in this group
	 */
	public function setIdentifierField($fieldName)
	{
		if (!isset($this->_fields[$fieldName])) {
			throw new \InvalidArgumentException(sprintf(
				'Field `%s` does not exist on this group.',
				$fieldName
			));
		}

		$this->_idFieldName	= $fieldName;

		return $this;
	}
}