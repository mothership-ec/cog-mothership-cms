<?php

namespace Message\Mothership\CMS\Field;

/**
 * Represents a group of fields on a page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Group
{
	protected $_fields;

	/**
	 * Constructor.
	 *
	 * @param array $fields Array of fields in this group
	 */
	public function __construct(array $fields)
	{
		foreach ($fields as $field) {
			$this->add($field);
		}
	}

	/**
	 * Add a field to this group.
	 *
	 * @param Field $field The field to add
	 */
	public function add(Field $field)
	{
		$this->_fields[] = $field;
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
}