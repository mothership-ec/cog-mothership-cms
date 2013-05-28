<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Event\Event;
use Message\Mothership\CMS\Page\Loader;
use Message\Mothership\CMS\Event\PageEvent;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;

/**
 * Decorator for deleting pages.
 *
 * @author Daniel Hannah <danny@message.co.uk>
 */
class Delete
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
	 * Delete a page by marking it as deleted in the DB
	 *
	 * @param  Page 	$page The page that needs to be deleted
	 * @return Page		The page that has been deleted
	 */
	public function delete(Page $page)
	{
		// Check that the page doesn't have children pages
		$loader = new Loader('gb', $this->_query);

		// Throw an exception if it does
		if ($loader->getChildren($page)) {
			throw new \Exception('Cannot delete page that has children pages.');
		}

		$page->authorship->delete(new \Datetime, 0);
		$result = $this->_query->run("
			UPDATE
				page
			SET
				deleted_at = ?i,
				deleted_by = ?i
			WHERE
				page_id = ?i
		", array(
			$page->authorship->deletedAt()->getTimestamp(),
			$page->authorship->deletedBy(),
			$page->id,
		));

		$this->_eventDispatcher->dispatch(
			PageEvent::DELETE,
			new PageEvent($page)
		);

		return $page;
	}


	/**
	 * Restores the currently deleted page to it's former self.
	 *
	 * @param Page 		$page instance of the deleted page to be reinstated
	 * @return Page 	$page instance of the page after it has been reinstated
	 */
	public function restore(Page $page)
	{
		$page->authorship->restore();

		$result = $this->_query->run("
			UPDATE
				page
			SET
				deleted_at = NULL,
				deleted_by = NULL
			WHERE
				page_id = ?i
		", array(
			$page->id,
		));

		$this->_eventDispatcher->dispatch(
			PageEvent::RESTORE,
			new PageEvent($page)
		);

		return $page;
	}
}