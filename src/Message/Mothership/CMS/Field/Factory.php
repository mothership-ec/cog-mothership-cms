<?php

namespace Message\Mothership\CMS\Field;

class Factory
{
	protected $_fields;

	public function addField($type, $name, $label = null)
	{
		$this->_add($this->getField($type, $name, $label));

		return $this;
	}

	public function addGroup($type, $label = null)
	{
		$this->_add($this->getGroup($name, $label));

		return $this;
	}

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

	public function getGroup($name, $label = null)
	{
		return new Group($name, $label);
	}

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