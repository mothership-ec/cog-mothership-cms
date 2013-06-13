<?php

namespace Message\Mothership\CMS\PageType;

/**
 * A container for all page types available to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Collection implements \IteratorAggregate, \Countable
{
	protected $_pageTypes = array();

	/**
	 * Constructor.
	 *
	 * @param array|null $pageTypes An array of page types to add
	 */
	public function __construct(array $pageTypes = array())
	{
		foreach ($pageTypes as $name => $pageType) {
			$this->add($pageType);
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
	public function add(\Message\Mothership\CMS\PageTypeInterface $pageType)
	{
		if (isset($this->_pageTypes[$pageType->getName()])) {
			throw new \InvalidArgumentException(sprintf('Page type `%s` is already defined', $pageType->getName()));
		}

		$this->_pageTypes[$pageType->getName()] = $pageType;

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
	public function get($name)
	{
		if (!isset($this->_pageTypes[$name])) {
			throw new \InvalidArgumentException(sprintf('Page type `%s` not set on collection', $name));
		}

		return $this->_pageTypes[$name];
	}

	/**
	 * Get the number of page types registered on this collection.
	 *
	 * @return int The number of page types registered
	 */
	public function count()
	{
		return count($this->_pageTypes);
	}

	/**
	 * Get the iterator object to use for iterating over this class.
	 *
	 * @return \ArrayIterator An \ArrayIterator instance for the `_pageTypes`
	 *                        property
	 */
	public function getIterator()
	{
		return new \ArrayIterator($this->_pageTypes);
	}
}