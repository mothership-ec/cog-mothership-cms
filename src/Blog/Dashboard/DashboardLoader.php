<?php

namespace Message\Mothership\CMS\Blog\Dashboard;

use Message\Mothership\CMS\Page;
use Message\Mothership\CMS\PageType;
use Message\Mothership\CMS\Blog;

class DashboardLoader
{
	/**
	 * @var Blog\CommentLoader
	 */
	private $_commentLoader;

	/**
	 * @var Page\Loader
	 */
	private $_pageLoader;

	private $_pages;

	private $_counts;

	private $_loaded = false;
	private $_aWeekAgo;
	private $_pageIDs;

	public function __construct(Blog\CommentLoader $commentLoader, Page\Loader $pageLoader, PageType\Collection $pageTypes)
	{
		$this->_commentLoader = $commentLoader;
		$this->_pageLoader    = $pageLoader;
		$this->_pageTypes     = $pageTypes;

		// Set to bypass all loading if no page types extend AbstractBlog
		$this->_blogInstalled();
	}

	public function getPages()
	{
		$this->_load();

		if (null === $this->_pages) {
			$this->_pages = $this->_pageLoader->getByID($this->_pageIDs);
		}

		return $this->_pages;
	}

	public function getPendingComments()
	{
		$this->_load();

		return $this->_counts[Blog\Statuses::PENDING];
	}

	public function getRecentlyApprovedComments()
	{
		$this->_load();

		return $this->_counts[Blog\Statuses::APPROVED];
	}

	private function _blogInstalled()
	{
		foreach ($this->_pageTypes as $pageType) {
			if ($pageType instanceof PageType\AbstractBlog) {
				return;
			}
		}

		$this->_loaded = true;

		$this->_counts = [
			Blog\Statuses::PENDING  => [],
			Blog\Statuses::APPROVED => [],
		];

		$this->_pages = [];
	}

	private function _load()
	{
		if (false === $this->_loaded) {
			$comments = $this->_commentLoader->getByStatus([
				Blog\Statuses::PENDING,
				Blog\Statuses::APPROVED
			]);

			$this->_counts = $this->_countPendingByPage($comments);

			$this->_loaded = true;
		}
	}

	private function _countPendingByPage(Blog\CommentCollection $comments)
	{
		if (null === $this->_counts) {
			$pendingCommentCount = [];
			$activeCommentCount  = [];

			foreach ($comments as $comment) {
				if ($comment->getStatus() === Blog\Statuses::PENDING) {
					$this->_addCommentToCount($comment, $pendingCommentCount);
				} elseif ($comment->getStatus === Blog\Statuses::APPROVED && $this->_commentFromThisWeek($comment)) {
					$this->_addCommentToCount($comment, $activeCommentCount);
				}
			}

			$this->_counts = [
				Blog\Statuses::PENDING  => $pendingCommentCount,
				Blog\Statuses::APPROVED => $activeCommentCount,
			];
		}

		return $this->_counts;
	}

	private function _addCommentToCount(Blog\Comment $comment, array &$count)
	{
		if (!array_key_exists($comment->getPageID(), $count)) {
			$count[$comment->getPageID()] = 0;
		}
		if (!in_array($comment->getPageID(), $this->_pageIDs)) {
			$this->_pageIDs[] = $comment->getPageID();
		}

		$count[$comment->getPageID()]++;
	}

	private function _commentFromThisWeek(Blog\Comment $comment)
	{
		if (null === $this->_aWeekAgo) {
			$this->_aWeekAgo = $this->_getDayTimestamp(new \DateTime('-1 week'));
		}

		return $this->_getDayTimestamp($comment->getCreatedAt()) >= $this->_aWeekAgo;
	}

	private function _getDayTimestamp(\DateTime $datetime)
	{
		$datetime->format('Y-m-d');

		return $datetime->getTimestamp();
	}
}