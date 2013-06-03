<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;
use Message\Mothership\CMS\Event\Event;
use Message\Mothership\CMS\Event\PageEvent;

use Message\Cog\Event\DispatcherInterface;
use Message\Cog\DB\Query as DBQuery;
use Message\Cog\DB\NestedSetHelper;

class Edit {

	protected $_loader;
	protected $_query;
	protected $_eventDispatcher;
	protected $_nestedSetHelper;

	public function __construct(Loader $loader, DBQuery $query,
		DispatcherInterface $eventDispatcher, NestedSetHelper $nestedSetHelper)
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
		$result = $this->_query->run('
			UPDATE
				file
			SET
				page.page_id = :id?i,
				page.title = :title?s,
				page.type = :type?s,
				page.publish_state = :publishState?i,
				page.publish_at = :publishAt?i,
				page.unpublish_at = :unpublishAt?i,
				page.created_at = :createdAt?i,
				page.created_by = :createdBy?i,
				page.updated_at = :updatedAt?i,
				page.created_by = :updatedBy?i,
				page.deleted_at = :deletedAt?i,
				page.deleted_by = :deletedBy?i,
				page.position_left = :`left`?i,
				page.position_right = :`right`?i,
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
				page_access_group.group_id = :accessGroups?i,
				page.comment_enabled = :commentsEnabled?i,
				page.comment_access = :commentsAccess?i,
				page.comment_access = :commentsAccessGroups?i,
				page.comment_approval = :commentsApproval?i,
				page.comment_expiry = :commentsExpiry?i',
				array(
					'locale' => $page->locale,
					'authorship' => $page->authorship,
					'id' => $page->id,
					'title' => $page->title,
					'type' => $page->type,
					'publishState' => $page->publishState,
					'publishDateRange' => $page->publishDateRange,
					'slug' => $page->slug,
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
		return $result->affected() ? $page : false;
	}

	public function publish(Page $page)
	{
		// If there is a unpublished date in the furture then keep it and set
		// publish date to now. If unpublish is in the past or null then set it
		// to null and
	}

	public function unpublish(Page $page)
	{

	}

}