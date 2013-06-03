<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;
use Message\Mothership\CMS\Event\Event;
use Message\Mothership\CMS\Event\PageEvent;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;
use Message\Cog\DB\NestedSetHelper;
use Message\Cog\ValueObject\DateRange;


class Edit {

	protected $_loader;
	protected $_query;
	protected $_eventDispatcher;
	protected $_nestedSetHelper;

	public function __construct(Loader $loader, DBQuery $query,
		DispatcherInterface $eventDispatcher, NestedSetHelper $nestedSetHelper = null)
	{
		$this->_loader          = $loader;
		$this->_query           = $query;
		$this->_eventDispatcher = $eventDispatcher;
		$this->_nestedSetHelper = $nestedSetHelper;
	}

	/**
	 * Pass thorugh the updated Page object and save it in the DB
	 *
	 * @param  Page   $page Page object to be update
	 * @return Page|false   Updated Page object
	 */
	public function save(Page $page)
	{

		// update the updated datetime
		$page->authorship->update(new \DateTime, 1);
// var_dump(array(
// 					'pageID' => $page->id,
// 					'title' => $page->title,
// 					'type' => $page->type,
// 					'publishState' => $page->publishState,
// 					'publishAt' => $page->publishDateRange->getStart()->getTimestamp(),
// 					'unpublishAt' => $page->publishDateRange->getEnd()->getTimestamp(),
// 					'updatedAt'	=> $page->authorship->updatedAt()->getTimestamp(),
// 					'updatedBy' => $page->authorship->updatedBy(),
// 					'slug' => $page->slug->getLastSegment(),
// 					'left' => $page->left,
// 					'right' => $page->right,
// 					'depth' => $page->depth,
// 					'metaTitle' => $page->metaTitle,
// 					'metaDescription' => $page->metaDescription,
// 					'metaHtmlHead' => $page->metaHtmlHead,
// 					'metaHtmlFoot' => $page->metaHtmlFoot,
// 					'visibilitySearch' => $page->visibilitySearch,
// 					'visibilityMenu' => $page->visibilityMenu,
// 					'visibilityAggregator' => $page->visibilityAggregator,
// 					'password' => $page->password,
// 					'access' => $page->access,
// 					'accessGroups' => $page->accessGroups,
// 					'commentsEnabled' => $page->commentsEnabled,
// 					'commentsAccess' => $page->commentsAccess,
// 					'commentsAccessGroups' => $page->commentsAccessGroups,
// 					'commentsApproval' => $page->commentsApproval,
// 					'commentsExpiry' => $page->commentsExpiry
// 		)); exit;
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
					'publishAt' => $page->publishDateRange->getStart()->getTimestamp(),
					'unpublishAt' => $page->publishDateRange->getEnd()->getTimestamp(),
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

		var_dump($page); exit;
		return $result->affected() ? $page : false;
	}

	public function publish(Page $page)
	{
		// If there is a unpublished date in the furture then keep it and set
		// publish date to now. If unpublish is in the past or null then set it
		// to null and
		$end = $page->publishDateRange->getEnd();

		if ($page->publishDateRange->getEnd()->getTimestamp() < time()) {
			$end = null;
		}

		$start = new \DateTime();

		$page->publishDateRange = new DateRange($start, $end);


		$page->publishAt = new \DateTime();
		$this->save($page);

		return $page;
	}

	public function unpublish(Page $page)
	{

	}

}