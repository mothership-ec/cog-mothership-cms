<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Event\Event;
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
		$result = $this->_query->run("
			UPDATE
				page
			SET
				deleted_at = UNIX_TIMESTAMP(),
				deleted_by = 0
			WHERE
				page_id = ?i
		", array(
			$page->id,
		));

		$loader = new Loader('gb', $this->_query);
		$page   = $loader->getByID($page->id);

		$this->_eventDispatcher->dispatch(
			Event::PAGE_DELETE,
			new PageEvent($page)
		);

		return $page;
	}
}