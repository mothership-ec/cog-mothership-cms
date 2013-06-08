<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;

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
 */
class Create
{
	protected $_loader;
	protected $_query;
	protected $_eventDispatcher;
	protected $_nestedSetHelper;

	/**
	 * Constructor.
	 *
	 * @param Loader              $loader          The page loader
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 * @param NestedSetHelper     $nestedSetHelper The nested set helper, set up
	 *                                             for the `Page` table
	 */
	public function __construct(Loader $loader, DBQuery $query,
		DispatcherInterface $eventDispatcher, NestedSetHelper $nestedSetHelper)
	{
		$this->_loader          = $loader;
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_nestedSetHelper = $nestedSetHelper;
	}

	/**
	 * Create a page.
	 *
	 * The newly created page is always added to the end of the target section.
	 *
	 * Once the page is created, the event defined as `Event::CREATE` is fired
	 * with the instance of the `Page` that was created. Whatever the event
	 * returns as the `Page` instance is then returned.
	 *
	 * @param  PageTypeInterface $pageType The page type to use for the page
	 * @param  string            $title    The page title
	 * @param  Page|null         $parent   The parent page, or null to create at
	 *                                     top level
	 *
	 * @return Page                        The page that was created (which may
	 *                                     have been overwritten by an event listener)
	 *
	 * @todo Throw an exception if the parent's page type does not allow child elements
	 */
	public function create(PageTypeInterface $pageType, $title, Page $parent = null)
	{
		#if ($parent && !$parent->type->allowChildPages) { // Is there a better property name? Is a property even good? What's the best waaaay?
			//throw exception
		#}

		// Create the page without adding it to the nested set tree
		$result = $this->_query->run('
			INSERT INTO
				page
			SET
				created_at    = UNIX_TIMESTAMP(),
				created_by    = 0,
				title         = :title?s,
				type          = :type?s,
				publish_state = 0
		', array(
			'title' => $title,
			'type'  => $pageType->getName(),
		));

		$pageID = (int) $result->id();

		// Add the page to the nested set tree
		$this->_nestedSetHelper->insertChildAtEnd($pageID, $parent ? $parent->id : false, true)->commit();

		$page  = $this->_loader->getByID($pageID);
		$event = new Event($page);

		// Dispatch the page created event
		$this->_eventDispatcher->dispatch(
			$event::CREATE,
			$event
		);

		// Return the Page instance set on the event
		return $event->getPage();
	}
}