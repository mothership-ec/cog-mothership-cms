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
	protected $_currentUser;

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
		$this->_currentUser		= $user;
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
	public function save(Page $page)
	{
		$page->authorship->update(new DateTimeImmutable, $this->_currentUser->id);

		$result = $this->_query->run(
			'UPDATE
				page
			SET
				page.title = :title?s,
				page.type = :type?s,
				page.publish_at = :publishAt?dn,
				page.unpublish_at = :unpublishAt?dn,
				page.updated_at = :updatedAt?dn,
				page.created_by = :updatedBy?i,
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
				'pageID'               => $page->id,
				'title'                => $page->title,
				'type'                 => $page->type->getName(),
				'publishAt'            => $page->publishDateRange->getStart(),
				'unpublishAt'          => $page->publishDateRange->getEnd(),
				'updatedAt'            => $page->authorship->updatedAt(),
				'updatedBy'            => $page->authorship->updatedBy(),
				'slug'                 => $page->slug->getLastSegment(),
				'left'                 => $page->left,
				'right'                => $page->right,
				'depth'                => $page->depth,
				'metaTitle'            => $page->metaTitle,
				'metaDescription'      => $page->metaDescription,
				'metaHtmlHead'         => $page->metaHtmlHead,
				'metaHtmlFoot'         => $page->metaHtmlFoot,
				'visibilitySearch'     => $page->visibilitySearch,
				'visibilityMenu'       => $page->visibilityMenu,
				'visibilityAggregator' => $page->visibilityAggregator,
				'password'             => $page->password,
				'access'               => $page->access,
				'accessGroups'         => $page->accessGroups,
				'commentsEnabled'      => $page->commentsEnabled,
				'commentsAccess'       => $page->commentsAccess,
				'commentsAccessGroups' => $page->commentsAccessGroups,
				'commentsApproval'     => $page->commentsApproval,
				'commentsExpiry'       => $page->commentsExpiry,
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
		return $this->_savePublishData($page);
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
		return $page;
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
		$result = $this->_query->run('
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
				$page->id
			)
		);

		return $result->affected() ? $page : false;

	}



}