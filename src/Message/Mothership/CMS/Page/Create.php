<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;

use Message\User\UserInterface;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;
use Message\Cog\DB\NestedSetHelper;

/**
 * Decorator for creating pages.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Create
{
	protected $_loader;
	protected $_query;
	protected $_eventDispatcher;
	protected $_nestedSetHelper;
	protected $_currentUser;

	/**
	 * Constructor.
	 *
	 * @param Loader              $loader          The page loader
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 * @param NestedSetHelper     $nestedSetHelper The nested set helper, set up
	 *                                             for the `Page` table
	 * @param UserInterface       $user            The currently logged in user
	 */
	public function __construct(Loader $loader, DBQuery $query, DispatcherInterface $eventDispatcher,
		NestedSetHelper $nestedSetHelper, UserInterface $user)
	{
		$this->_loader          = $loader;
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_nestedSetHelper = $nestedSetHelper;
		$this->_currentUser     = $user;
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
	 * @throws \InvalidArgumentException If the parent page's type does not allow
	 *                                   child pages
	 */
	public function create(PageTypeInterface $pageType, $title, Page $parent = null)
	{
		if ($parent && !$parent->type->allowChildren()) {
			throw new \InvalidArgumentException(sprintf(
				'Cannot create a page within page #%i because it\'s type (%s) does not allow child pages.',
				$parent->id,
				$parent->type->getName()
			);
		}

		// Create the page without adding it to the nested set tree
		$result = $this->_query->run('
			INSERT INTO
				page
			SET
				created_at    = UNIX_TIMESTAMP(),
				created_by    = :createdBy?in,
				title         = :title?s,
				type          = :type?s,
				unpublish_at  = UNIX_TIMESTAMP()
		', array(
			'title'     => $title,
			'type'      => $pageType->getName(),
			'createdBy' => $this->_currentUser->id,
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