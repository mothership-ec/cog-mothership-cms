<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Event\Event;
use Message\Mothership\CMS\Page\Loader;

use Message\User\UserInterface;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Decorator for deleting & restoring pages.
 *
 * @author Danny Hannah <danny@message.co.uk>
 */
class Delete
{
	protected $_query;
	protected $_eventDispatcher;
	protected $_loader;

	/**
	 * Constructor.
	 *
	 * @param DBQuery             $query           The database query instance to use
	 * @param DispatcherInterface $eventDispatcher The event dispatcher
	 * @param Loader              $loader          The page loader
	 * @param UserInterface       $currentUser     The currently logged in user
	 */
	public function __construct(DBQuery $query, DispatcherInterface $eventDispatcher,
		Loader $loader, UserInterface $user)
	{
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_loader          = $loader;
		$this->_currentUser     = $user;
	}

	/**
	 * Delete a page by marking it as deleted in the database.
	 *
	 * @param  Page $page The page to be deleted
	 *
	 * @return Page       The page that was been deleted, with the "delete"
	 *                    authorship data set
	 *
	 * @throws \InvalidArgumentException If the page has child pages
	 */
	public function delete(Page $page)
	{
		if ($this->_loader->getChildren($page)) {
			throw new \InvalidArgumentException(sprintf('Cannot delete page #%i because it has child pages', $page->id));
		}

		$page->authorship->delete(new DateTimeImmutable, $this->_currentUser->id);

		$result = $this->_query->run('
			UPDATE
				page
			SET
				deleted_at = :at?i,
				deleted_by = :by?in
			WHERE
				page_id = :id?i
		', array(
			'at' => $page->authorship->deletedAt()->getTimestamp(),
			'by' => $page->authorship->deletedBy(),
			'id' => $page->id,
		));

		$event = new Event($page);

		$this->_eventDispatcher->dispatch(
			$event::DELETE,
			$event
		);

		return $event->getPage();
	}


	/**
	 * Restores a currently deleted page to its former self.
	 *
	 * @param Page page   The deleted page to be restored
	 *
	 * @return Page $page The restored page, with the "delete" authorship data
	 *                    cleared
	 */
	public function restore(Page $page)
	{
		$page->authorship->restore();

		$result = $this->_query->run('
			UPDATE
				page
			SET
				deleted_at = NULL,
				deleted_by = NULL
			WHERE
				page_id = ?i
		', $page->id);

		$event = new Event($page);

		$this->_eventDispatcher->dispatch(
			$event::RESTORE,
			$event
		);

		return $event->getPage();
	}
}