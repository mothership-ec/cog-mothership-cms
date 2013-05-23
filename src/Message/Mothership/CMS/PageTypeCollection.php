<?php

namespace Message\Mothership\CMS;

/**
 * A container for all page types available to the system.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class PageTypeCollection implements \IteratorAggregate, \Countable
{
	protected $_pageTypes = array();

	/**
	 * Constructor.
	 *
	 * @param array|null $pageTypes An array of page types to add
	 */
	public function __construct(array $pageTypes = null)
	{
		if (is_array($pageTypes)) {
			foreach ($pageTypes as $name => $pageType) {
				$this->add($pageType);
			}
		}
	}

	/**
	 * Add a page type to this collection.
	 *
	 * @param PageTypeInterface $pageType The page type to add
	 *
	 * @return PageTypeCollection         Returns $this for chainability
	 */
	public function add(PageTypeInterface $pageType)
	{
		$this->_pageTypes[] = $pageType;

		return $this;
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