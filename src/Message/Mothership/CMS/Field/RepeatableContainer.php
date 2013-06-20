<?php

namespace Message\Mothership\CMS\Field;

/**
 * Wrapper for a repeatable set of page field groups.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RepeatableContainer implements \IteratorAggregate, \Countable
{
	protected $_groups = array();

	/**
	 * Constructor.
	 *
	 * @param array $groups Array of field groups
	 */
	public function __construct(array $groups = array())
	{
		foreach ($groups as $group) {
			$this->add($group);
		}
	}

	/**
	 * Add a group to this repeatable set
	 *
	 * @param Group $group The group to add
	 */
	public function add(Group $group)
	{
		$this->_groups[] = $group;
	}

	/**
	 * Get the number of groups in this repeatable set.
	 *
	 * @return int Number of groups
	 */
	public function count()
	{
		return count($this->_groups);
	}

	/**
	 * Get a group at a specific index from this container.
	 *
	 * @param  int $index  The index
	 *
	 * @return Group|false The group instance, or false if it doesn't exist
	 */
	public function get($index)
	{
		$index = (int) $index;

		return isset($this->_groups[$index]) ? $this->_groups[$index] : false;
	}

	/**
	 * Get the iterator to use for this iterable class.
	 *
	 * @return \ArrayIterator The iterator to use
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_groups);
	}
}