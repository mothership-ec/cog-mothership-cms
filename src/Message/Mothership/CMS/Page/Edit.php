<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;
use Message\Mothership\CMS\Page\Event;
use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;
use Message\Cog\DB\NestedSetHelper;
use Message\Cog\ValueObject\DateRange;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\User\UserInterface;
use Message\User\User;


class Edit {

	protected $_loader;
	protected $_query;
	protected $_eventDispatcher;
	protected $_nestedSetHelper;

	public function __construct(
		Loader $loader,
		DBQuery $query,
		DispatcherInterface $eventDispatcher,
		NestedSetHelper $nestedSetHelper,
		UserInterface $user)
	{
		$this->_loader          = $loader;
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_nestedSetHelper = $nestedSetHelper;
	}

	/**
	 * Pass through the updated Page object and save it in the DB
	 *
	 * @todo Need o do something with the nested set helper when moving a page
	 *       and things
	 *
	 * @param  Page   		$page 	Page object to be update
	 *
	 * @return Page|false   		Updated Page object
	 */
	public function save(Page $page, User $user)
	{
		// update the updated datetime
		$page->authorship->update(new DateTimeImmutable, $user->id);

		$result = $this->_query->run('
			UPDATE
				page
			SET
				page.title = :title?s,
				page.type = :type?s,
				page.publish_state = :publishState?i,
				page.publish_at = :publishAt?i,
				page.unpublish_at = :unpublishAt?i,
				page.updated_at = :updatedAt?i,
				page.created_by = :updatedBy?i,
				page.deleted_at = :deletedAt?i,
				page.deleted_by = :deletedBy?i,
				page.position_left = :left?i,
				page.position_right = :right?i,
				page.position_depth = :depth?i,
				page.meta_title = :metaTitle?s,
				page.meta_description = :metaDescription?s,
				page.meta_html_head = :metaHtmlHead?s,
				page.meta_html_foot = :metaHtmlFoot?s,
				page.visibility_search = :visibilitySearch?i,
				page.visibility_menu = :visibilityMenu?i,
				page.visibility_aggregator = :visibilityAggregator?i,
				page.password = :password?s,
				page.access = :access?s,
				/*
				page_access_group.group_id = :accessGroups?i,
				*/
				page.comment_enabled = :commentsEnabled?i,
				page.comment_access = :commentsAccess?i,
				page.comment_access = :commentsAccessGroups?i,
				page.comment_approval = :commentsApproval?i,
				page.comment_expiry = :commentsExpiry?i
			WHERE
				page.page_id = :pageID?i',
				array(
					'pageID' => $page->id,
					'title' => $page->title,
					'type' => $page->type,
					'publishState' => $page->publishState,
					'publishAt' => $page->publishDateRange->getStart() ? $page->publishDateRange->getStart()->getTimestamp() : null,
					'unpublishAt' => $page->publishDateRange->getEnd() ? $page->publishDateRange->getEnd()->getTimestamp() : null,
					'updatedAt'	=> $page->authorship->updatedAt()->getTimestamp(),
					'updatedBy' => $page->authorship->updatedBy(),
					'slug' => $page->slug->getLastSegment(),
					'left' => $page->left,
					'right' => $page->right,
					'depth' => $page->depth,
					'metaTitle' => $page->metaTitle,
					'metaDescription' => $page->metaDescription,
					'metaHtmlHead' => $page->metaHtmlHead,
					'metaHtmlFoot' => $page->metaHtmlFoot,
					'visibilitySearch' => $page->visibilitySearch,
					'visibilityMenu' => $page->visibilityMenu,
					'visibilityAggregator' => $page->visibilityAggregator,
					'password' => $page->password,
					'access' => $page->access,
					'accessGroups' => $page->accessGroups,
					'commentsEnabled' => $page->commentsEnabled,
					'commentsAccess' => $page->commentsAccess,
					'commentsAccessGroups' => $page->commentsAccessGroups,
					'commentsApproval' => $page->commentsApproval,
					'commentsExpiry' => $page->commentsExpiry
		));

		$event = new Event($page);

		// Dispatch the edit event
		$this->_eventDispatcher->dispatch(
			Event::EDIT,
			$event
		);

		return $event->getpage();
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
	 * @param  User 	$user 	User who initiated action
	 *
	 * @return Page 	$page 	Updated page object
	 */
	public function publish(Page $page, User $user = null)
	{
		// Get the end data if there is one
		$end = $page->publishDateRange->getEnd();
		// If the end date is in the past then set it to null
		if ($page->publishDateRange->getEnd()->getTimestamp() < time()) {
			$end = null;
		}
		// Create a start date from now
		$start = new DateTimeImmutable;
		// Build the date range object with the new dates and assign it to
		// the page object
		$page->publishDateRange = new DateRange($start, $end);
		// Set the publish state to 1
		$page->publishState = 1;
		// Save the page to the DB
		$this->save($page, $user);
		// Return the updated page object
		return $page;
	}

	/**
	 * Update the page to be unpublished
	 *
	 * @param  Page   	$page Page to update as unpublished
	 * @param  User 	$user User of who invoked the action
	 *
	 * @return Page   	$page Updated Page object
	 */
	public function unpublish(Page $page, User $user = null)
	{
		// Set the end time to now
		$end = new DateTimeImmutable;
		$start = $page->publishDateRange->getStart();

		// If the start date is in the new end time then set it to null
		if ($page->publishDateRange->getStart()->getTimestamp() > $end->getTimestamp()) {
			$start = null;
		}
		// Set the new unpublsih date range
		$page->publishDateRange = new DateRange($start, $end);
		// Se tthe publish state to 0
		$page->publishState = 0;
		// Save the page to the DB
		$this->save($page, $user);
		// Return the updated Page object
		return $page;
	}

}