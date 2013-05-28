<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;
use Message\Mothership\CMS\Event\Event;
use Message\Mothership\CMS\Event\PageEvent;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;
use Message\Cog\DB\NestedSetHelper;

/**
 * Decorator for creating pages.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 *
 * @todo Implement the created_by setting. Is there a service for the current
 *       user?
 * @todo Pass the default Locale to Loader when it's instantiated and we have it
 */
class Create
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_nestedSetHelper;

	/**
	 * Constructor.
	 *
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 * @param NestedSetHelper     $nestedSetHelper The nested set helper, set up
	 *                                             for the `Page` table
	 */
	public function __construct(DBQuery $query, DispatcherInterface $eventDispatcher, NestedSetHelper $nestedSetHelper)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_nestedSetHelper = $nestedSetHelper;
	}

	/**
	 * Create a page.
	 *
	 * The newly created page is always added to the end of the target section.
	 *
	 * Once the page is created, the event defined as `Event\PageEvent::CREATE`
	 * is fired with the instance of the `Page` that was created. Whatever the
	 * event returns as the `Page` instance is then returned.
	 *
	 * @param  PageTypeInterface $pageType The page type to use for the page
	 * @param  string            $title    The page title
	 * @param  Page|null         $parent   The parent page, or null to create at
	 *                                     top level
	 *
	 * @return Page                        The page that was created (which may
	 *                                     have been overwritten by an event listener)
	 */
	public function create(PageTypeInterface $pageType, $title, Page $parent = null)
	{
		// Create the page without adding it to the nested set tree
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

		$loader = new Loader('the locale thing', $this->_query);
		$pageID = (int) $result->id();

		// Add the page to the nested set tree
		$this->_nestedSetHelper->insertChildAtEnd($pageID, $parent ? $parent->id : false, true)->commit();

		$page  = $loader->getByID($pageID);
		$event = new PageEvent($page);

		// Dispatch the page created event
		$this->_eventDispatcher->dispatch(
			PageEvent::CREATE,
			$event
		);

		// Return the Page instance set on the event
		return $event->getPage();
	}
}