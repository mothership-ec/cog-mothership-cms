<?php

namespace Message\Mothership\CMS\Field;

class Repeatable implements \IteratorAggregate, \Countable
{
	protected $_groups;

	public function __construct(array $groups)
	{
		foreach ($groups as $group) {
			$this->add($group);
		}
	}

	public function add(Group $group)
	{
		$this->_groups[] = $group;
	}

	public function count()
	{
		return count($this->_groups);
	}

	public function getIterator()
	{
		return \ArrayIterator($this->_groups);
	}
}