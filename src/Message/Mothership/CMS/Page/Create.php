<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;
use Message\Mothership\CMS\Event\Event;
use Message\Mothership\CMS\Event\PageEvent;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;

/**
 * Decorator for creating pages.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 *
 * @todo Implement the created_by setting. Is there a service for the current
 *       user?
 * @todo Pass the default Locale to Loader when it's instantiated and we have it
 * @todo Set the position using NestedSetHelper
 */
class Create
{
	protected $_query;
	protected $_eventDispatcher;

	/**
	 * Constructor.
	 *
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 */
	public function __construct(DBQuery $query, DispatcherInterface $eventDispatcher)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
	}

	/**
	 * Create a page.
	 *
	 * @param  PageTypeInterface $pageType The page type to use for the page
	 * @param  string            $title    The page title
	 *
	 * @return Page                        The page that was created
	 */
	public function create(PageTypeInterface $pageType, $title)
	{
		$result = $this->_query->run("
			INSERT INTO
				page
			SET
				created_at    = UNIX_TIMESTAMP(),
				created_by    = 0,
				title         = ?s,
				type          = ?s,
				publish_state = 0
		", array(
			$title,
			$pageType->getName(),
		));

		// add it to the tree. we need to know the parent page

		$loader = new Loader('the locale thing', $this->_query);
		$page   = $loader->getByID($result->id());

		$this->_eventDispatcher->dispatch(
			Event::PAGE_CREATE,
			new PageEvent($page)
		);

		return $page;
	}
}