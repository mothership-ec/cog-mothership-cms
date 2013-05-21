<?php

namespace Message\Mothership\CMS\Field;

class Group
{
	protected $_fields;

	public function __construct(array $fields)
	{
		foreach ($fields as $field) {
			$this->add($field);
		}
	}

	public function add(Field $field)
	{
		$this->_fields[] = $field;
	}

	public function __get($name)
	{
		if (isset($this->_fields[$name])) {
			return $this->_fields[$name];
		}

		throw new \OutOfBoundsException(sprintf('Group field does not exist: `%s`', $name));
	}
}