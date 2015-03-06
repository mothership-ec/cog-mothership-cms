<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Mothership\CMS\Page\Event;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Transaction;
use Message\Cog\DB\TransactionalInterface;
use Message\Cog\DB\NestedSetHelper;
use Message\Cog\ValueObject\DateRange;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\Slug;
use Message\User\UserInterface;
use Message\User\User;


class Edit implements TransactionalInterface
{

	/**
	 * @var \Message\Cog\DB\Transaction
	 * @deprecated Renamed to $_transaction. Can probably be deleted but deprecating in case this class is extended anywhere
	 */
	protected $_query;

	protected $_loader;
	protected $_transaction;
	protected $_eventDispatcher;
	protected $_nestedSetHelper;
	protected $_currentUser;

	private $_transOverride = false;

	public function __construct(
		Loader $loader,
		Transaction $transaction,
		DispatcherInterface $eventDispatcher,
		NestedSetHelper $nestedSetHelper,
		UserInterface $user)
	{
		$this->_loader          = $loader;
		$this->_transaction     = $transaction;
		$this->_query           = $transaction;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_nestedSetHelper = $nestedSetHelper;
		$this->_currentUser		= $user;

		$this->_nestedSetHelper->setTransaction($this->_transaction);
	}

	public function setTransaction(Transaction $transaction)
	{
		$this->_transaction = $transaction;
		$this->_nestedSetHelper->setTransaction($this->_transaction);
		$this->_transOverride = true;

		return $this;
	}

	/**
	 * Pass through the updated Page object and save it in the DB
	 *
	 * @param  Page   		$page 	Page object to be update
	 *
	 * @return Page|false   		Updated Page object
	 */
	public function save(Page $page)
	{
		$page->authorship->update(new DateTimeImmutable, $this->_currentUser->id);

		$this->_transaction->run(
			'UPDATE
				page
			SET
				page.title                 = :title?s,
				page.type                  = :type?s,
				page.publish_at            = :publishAt?dn,
				page.unpublish_at          = :unpublishAt?dn,
				page.updated_at            = :updatedAt?dn,
				page.updated_by            = :updatedBy?i,
				page.meta_title            = :metaTitle?s,
				page.meta_description      = :metaDescription?s,
				page.meta_html_head        = :metaHtmlHead?s,
				page.meta_html_foot        = :metaHtmlFoot?s,
				page.visibility_search     = :visibilitySearch?i,
				page.visibility_menu       = :visibilityMenu?i,
				page.visibility_aggregator = :visibilityAggregator?i,
				page.password              = :password?s,
				page.access                = :access?s
			WHERE
				page.page_id = :pageID?i',
			array(
				'pageID'               => $page->id,
				'title'                => $page->title,
				'type'                 => $page->type->getName(),
				'publishAt'            => $page->publishDateRange->getStart(),
				'unpublishAt'          => $page->publishDateRange->getEnd(),
				'updatedAt'            => $page->authorship->updatedAt(),
				'updatedBy'            => $page->authorship->updatedBy(),
				'slug'                 => $page->slug->getLastSegment(),
				'metaTitle'            => $page->metaTitle,
				'metaDescription'      => $page->metaDescription,
				'metaHtmlHead'         => $page->metaHtmlHead,
				'metaHtmlFoot'         => $page->metaHtmlFoot,
				'visibilitySearch'     => $page->visibilitySearch,
				'visibilityMenu'       => $page->visibilityMenu,
				'visibilityAggregator' => $page->visibilityAggregator,
				'password'             => $page->password,
				'access'               => $page->access,
			)
		);

		// Update the user groups for this page in the DB
		$this->_updateAccessGroups($page);
		$this->_updateTags($page);

		$event = new Event\Event($page);
		// Dispatch the edit event
		$this->_eventDispatcher->dispatch(
			$event::EDIT,
			$event
		);

		if (!$this->_transOverride) {
			$this->_transaction->commit();
		}

		return $event->getPage();
	}

	/**
	 * Update the slug and insert the old slug into the historical slug table
	 *
	 * @param  Page   $page    	Page object to udpate
	 * @param  string $newSlug  The new slug to update
	 *
	 * @return Page          	Return the updated Page object
	 */
	public function updateSlug(Page $page, $newSlug)
	{
		// Get all the segements
		$segements = $page->slug->getSegments();
		$date = new DateTimeImmutable;
		$this->_transaction->run('
			REPLACE INTO
				page_slug_history
			SET
				page_id = ?i,
				slug 	= ?s,
				created_at = ?d,
				created_by = ?i',
			array(
				$page->id,
				$page->slug->getFull(),
				$date,
				$this->_currentUser->id,
			)
		);

		$this->_transaction->run('
			UPDATE
				page
			SET
				slug = ?s
			WHERE
				page_id = ?i',
			array(
				$newSlug,
				$page->id,
			)
		);
		// Remove the last one
		$last = array_pop($segements);
		// Set the new one to the end of the array
		$segments[] = $newSlug;
		// Create a new slug object
		$slug = new Slug($segments);
		// Add it to the page object
		$page->slug = $slug;

		$this->_transaction->commit();

		return $page;
	}

	/**
	 * Remove a given slug from the page_slug_history table
	 *
	 * @param  string 	$slug 	The slug to remove
	 */
	public function removeHistoricalSlug($slug)
	{
		$this->_transaction->run('
			DELETE FROM
				page_slug_history
			WHERE
				slug = ?s
		', array(
			$slug
		));

		$this->_transaction->commit();
	}

	/**
	 * Set the page as Published
	 *
	 * If there is a unpublished date in the future then keep it and set
	 * publish date to now.
	 * If unpublish is in the past or null then set it to null so it won't
	 * unpublish itself.
	 *
	 * @param  Page   	$page   Page to update as Published
	 *
	 * @return Page 	$page 	Updated page object
	 */
	public function publish(Page $page)
	{
		// Get the end data if there is one
		$end = $page->publishDateRange->getEnd();
		// If the end date is in the past then set it to null
		if ($end && $end->getTimestamp() < time()) {
			$end = null;
		}
		// Create a start date from now
		$start = new DateTimeImmutable;
		// Build the date range object with the new dates and assign it to
		// the page object
		$page->publishDateRange = new DateRange($start, $end);
		// Save the page to the DB
		$page = $this->_savePublishData($page);

		$this->_transaction->commit();

		return $page;
	}

	/**
	 * Update the page to be unpublished
	 *
	 * @param  Page   	$page Page to update as unpublished
	 *
	 * @return Page   	$page Updated Page object
	 */
	public function unpublish(Page $page)
	{
		// Set the end time to now
		$end = new DateTimeImmutable;
		$start = $page->publishDateRange->getStart();

		// If the start date is in the new end time then set it to null
		if ($start && $start->getTimestamp() > $end->getTimestamp()) {
			$start = null;
		}
		// Set the new unpublsih date range
		$page->publishDateRange = new DateRange($start, $end);
		// Save the page to the DB
		$this->_savePublishData($page);
		// Return the updated Page object

		$this->_transaction->commit();

		return $page;
	}

	/**
	 * Change the order of the children within a nested set. This would also move
	 * the children nodes of any entry that is affected by the move.
	 *
	 * @param  Page 	$page 				The Page object of the page we are
	 *                         				going to move
	 * @param  int  	$index				The position index to move to.
	 */
	public function changeOrder(Page $page, $index)
	{
		// This is important as we add 1 to the key
		try {

			$siblings = $this->_loader
				->getSiblings($page);
			// We minus one here as we have to add one in the controller so 0 is
			// the move to top option.
			$nearestSibling = isset($siblings[$index - 1]) ? $siblings[$index - 1]->id : false;

			$addAfter = false;
			if ($index === 0) {
				// Load the siblings and get the one which is at the top
				$siblings = $this->_loader->getSiblings($page);
				$nearestSibling = array_shift($siblings);
				$addAfter = true;
			} else {
				// Otherwise just load the given sibling to move the page after
				$nearestSibling = $this->_loader->getByID($nearestSibling);
			}

			$this->_nestedSetHelper->move(
				$page->id,
				$nearestSibling->id,
				false,
				$addAfter
			);
			$this->_transaction->commit();

			return true;
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * This will move a node to a different parent of the tree.
	 *
	 * @param int 	$pageID 		The ID of the page we are going to move
	 * @param int   $newParentID 	The ID of the new parent we are moving to
	 */
	public function changeParent($pageID, $newParentID)
	{
		try {
			$this->_nestedSetHelper->move($pageID, $newParentID, true);
			$this->_transaction->commit();

			return true;
		} catch (\Exception $e) {
			return false;
		}
	}

	/**
	 * Save only the publish data in the DB
	 *
	 * @param  Page   	$page 	page object to be updated
	 *
	 * @return Page|false 		returns page object if successful or false if not.
	 */
	protected function _savePublishData(Page $page)
	{
		$this->_transaction->run('
			UPDATE
				page
			SET
				publish_at = ?dn,
				unpublish_at = ?dn
			WHERE
				page_id = ?i
			', array(
				$page->publishDateRange->getStart(),
				$page->publishDateRange->getEnd(),
				$page->id,
			)
		);

		return $page;
	}

	/**
	 * Update the database with the user groups for this page.
	 *
	 * @param  Page   $page Page object to update
	 */
	protected function _updateAccessGroups(Page $page)
	{
		// Remove any existing access groups as groups may havge been unselected
		$this->_transaction->run(
			'DELETE FROM
				page_access_group
			WHERE
				page_id = ?i',
			array(
				$page->id
			)
		);

		// Build the insert query and parameters
		$inserts = array();
		$values = array();
		foreach ($page->accessGroups as $groupName) {
			$inserts[] = '(?i, ?s)';
			$values[] = $page->id;
			$values[] = $groupName;
		}

		// If there is changes to be made then run the built query
		if ($values) {
			$this->_transaction->run(
				'INSERT INTO
					page_access_group
					(page_id, group_name)
				VALUES
					'.implode(',',$inserts).'
				', $values
			);
		}

	}

	protected function _updateTags(Page $page)
	{
		$this->_transaction->run("
				DELETE FROM
					page_tag
				WHERE
					page_id = :pageId?i
			", [
			'pageId' => $page->id
		]);

		$tags = $page->getTags();
		if (!empty($tags)) {
			foreach ($tags as $tag) {
				$this->_transaction->run("
					INSERT INTO
						page_tag
						(
							page_id,
							tag_name
						)
					VALUES
						(
							:pageID?i,
							:tag?s
						)
				", [
					'pageID' => $page->id,
					'tag'    => $tag,
				]);
			}
		}

	}
}