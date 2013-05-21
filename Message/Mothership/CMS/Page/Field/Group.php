<?php

namespace Message\Mothership\CMS\Page\Field;

/**
 * Represents a group of fields on a page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Group
{
	protected $_fields = array();

	/**
	 * Constructor.
	 *
	 * @param array $fields Array of fields in this group
	 */
	public function __construct(array $fields = array())
	{
		foreach ($fields as $name => $field) {
			$this->add($name, $field);
		}
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
}