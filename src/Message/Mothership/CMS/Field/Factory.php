<?php

namespace Message\Mothership\CMS\Field;

/**
 * Field factory, for building fields and groups of fields.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Factory
{
	protected $_fields;

	/**
	 * Add a new field to the factory.
	 *
	 * @see _add
	 *
	 * @param  string      $type  The field type to get
	 * @param  string      $name  The name to use for the field
	 * @param  string|null $label The optional label for the field
	 *
	 * @return Factory            Returns $this for chainability
	 */
	public function addField($type, $name, $label = null)
	{
		$this->_add($this->getField($type, $name, $label));

		return $this;
	}

	/**
	 * Add a new group to the factory.
	 *
	 * @see _add
	 *
	 * @param  string      $name  The name to use for the group
	 * @param  string|null $label The optional label for the group
	 *
	 * @return Factory            Returns $this for chainability
	 */
	public function addGroup($type, $label = null)
	{
		$this->_add($this->getGroup($name, $label));

		return $this;
	}

	/**
	 * Get a new instance of a field.
	 *
	 * @param  string      $type  The field type to get
	 * @param  string      $name  The name to use for the field
	 * @param  string|null $label The optional label for the field
	 *
	 * @return Group
	 *
	 * @throws \InvalidArgumentException If the field type does not exist
	 */
	public function getField($type, $name, $label = null)
	{
		$className = __NAMESPACE__ . '\\Type\\' . ucfirst($type);

		// Check if a class exists for this field type
		if (!class_exists($className)) {
			throw new \InvalidArgumentException(
				'Field type `%s` does not exist (class `%s` not found)',
				$type,
				$className
			);
		}

		return new $className($name, $label);
	}

	/**
	 * Get a new instance of a group field.
	 *
	 * @param  string      $name  The name to use for the group
	 * @param  string|null $label The optional label for the group
	 *
	 * @return Group
	 */
	public function getGroup($name, $label = null)
	{
		return new Group($name, $label);
	}

	/**
	 * Add a field or a field group to the factory.
	 *
	 * @param FieldInterface $field The field or group to add
	 *
	 * @throws \InvalidArgumentException If a field with the identifier returned
	 *                                   from `getName()` on the field already exists
	 */
	protected function _add(FieldInterface $field)
	{
		// Check if a field with this name already exists
		if (isset($this->_fields[$field->getName()])) {
			throw new \InvalidArgumentException(sprintf(
				'A field with the name `%s` already exists on the field factory',
				$field->getName()
			));
		}

		$this->_fields[$field->getName()] = $field;
	}
}