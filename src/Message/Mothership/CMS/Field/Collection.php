<?php

namespace Message\Mothership\CMS\Field;

/**
 * A container for all field types available to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable
{
	protected $_fields = array();

	/**
	 * Constructor.
	 *
	 * @param array|null $fields An array of fields to add
	 */
	public function __construct(array $fields = array())
	{
		foreach ($fields as $name => $fields) {
			$this->add($fields);
		}
	}

	/**
	 * Add a page type to this collection.
	 *
	 * @param PageTypeInterface $pageType The page type to add
	 *
	 * @return PageTypeCollection         Returns $this for chainability
	 *
	 * @throws \InvalidArgumentException  If a page type with the same name has
	 *                                    already been set on this collection
	 */
	public function add(FieldInterface $field)
	{
		if (isset($this->_fields[$field->getType()])) {
			throw new \InvalidArgumentException(sprintf('Page type `%s` is already defined', $pageType->getName()));
		}

		$this->_fields[$pageType->getType()] = $field;

		return $this;
	}

	/**
	 * Get a page type set on this collection by name.
	 *
	 * @param  string $name      The page type name
	 *
	 * @return PageTypeInterface The page type instance
	 *
	 * @throws \InvalidArgumentException If the page type has not been set
	 */
	public function get($type)
	{
		if (!isset($this->_fields[$type])) {
			throw new \InvalidArgumentException(sprintf('Page type `%s` not set on collection', $name));
		}

		return $this->_fields[$type];
	}

	/**
	 * Get the number of page types registered on this collection.
	 *
	 * @return int The number of page types registered
	 */
	public function count()
	{
		return count($this->_fields);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_pageTypes`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_fields);
	}
}