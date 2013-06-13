<?php

namespace Message\Mothership\CMS\Field;

/**
 * Represents a group of fields on a page.
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
	 * @see add()
	 */
	public function __set($name, Field $field)
	{
		$this->add($name, $field);
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
	 * Add a field to this group.
	 *
	 * @param string $name  The name for the field
	 * @param Field  $field The field to add
	 *
	 * @throws \InvalidArgumentException If the name is falsey
	 */
	public function add($name, Field $field)
	{
		if (!$name) {
			throw new \InvalidArgumentException('Page field group field must have a name');
		}

		$this->_fields[$name] = $field;
	}

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

	public function getIdentifierField()
	{
		if (!$this->_idFieldName || !isset($this->_fields[$this->_idFieldName])) {
			throw new \LogicException('No identifier field has been set yet.');
		}

		return $this->_fields[$name];
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
}