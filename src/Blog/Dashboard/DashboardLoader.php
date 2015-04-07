<?php

namespace Message\Mothership\CMS\Blog\Dashboard;

use Message\Mothership\CMS\Page;
use Message\Mothership\CMS\PageType;
use Message\Mothership\CMS\Blog;

/**
 * Class DashboardLoader
 * @package Message\Mothership\CMS\Blog\Dashboard
 *
 * @author Thomas Marchant <thomas@mothership.ec>
 *
 * Class for loading the appropriate data to display on the comment dashboard panel:
 * - Pending comments
 * - Comments approved within the last 7 days
 * - Pages with either pending comments or recently approved comments
 */
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

	/**
	 * @var null | array
	 */
	private $_pages;

	/**
	 * Array containing an array of page IDs with pending comment counts, and an array of page IDs with recently
	 * approved comment counts
	 *
	 * @var array
	 */
	private $_counts;

	/**
	 * @var bool
	 */
	private $_loaded = false;

	/**
	 * The unix timestamp for 00:00 7 days ago
	 *
	 * @var int
	 */
	private $_aWeekAgo;

	/**
	 * @var array
	 */
	private $_pageIDs = [];

	public function __construct(Blog\CommentLoader $commentLoader, Page\Loader $pageLoader, PageType\Collection $pageTypes)
	{
		$this->_commentLoader = $commentLoader;
		$this->_pageLoader    = $pageLoader;
		$this->_pageTypes     = $pageTypes;

		// Set to bypass all loading if there is no blog
		if (!$this->_blogInstalled()) {
			$this->_loadEmpty();
		}
	}

	/**
	 * Get an array of pages that have either pending comments or recently approved comments, with page IDs for keys
	 *
	 * @return array
	 */
	public function getPages()
	{
		$this->_load();

		if (null === $this->_pages) {
			$pages = $this->_pageLoader->getByID($this->_pageIDs);
			$this->_pages = [];

			foreach ($pages as $page) {
				$this->_pages[$page->id] = $page;
			}
		}

		return $this->_pages;
	}

	/**
	 * Get the number of pending comments for each page
	 *
	 * @return array
	 */
	public function getPendingCounts()
	{
		$this->_load();

		return $this->_counts[Blog\Statuses::PENDING];
	}

	/**
	 * Get the number of recently approved comments for each page
	 *
	 * @return array
	 */
	public function getRecentlyApprovedCounts()
	{
		$this->_load();

		return $this->_counts[Blog\Statuses::APPROVED];
	}

	/**
	 * Check to see if any page types extend AbstractBog
	 *
	 * @return bool
	 */
	private function _blogInstalled()
	{
		foreach ($this->_pageTypes as $pageType) {
			if ($pageType instanceof PageType\AbstractBlog) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Load all comment counts
	 */
	private function _load()
	{
		if (false === $this->_loaded) {
			$comments = $this->_commentLoader->getByStatus([
				Blog\Statuses::PENDING,
				Blog\Statuses::APPROVED
			], 'DESC');

			$this->_counts = $this->_countCommentsByPage($comments);

			$this->_loaded = true;
		}
	}

	/**
	 * Bypass loading and set all counts to empty
	 */
	private function _loadEmpty()
	{
		$this->_loaded = true;

		$this->_counts = [
			Blog\Statuses::PENDING  => [],
			Blog\Statuses::APPROVED => [],
		];

		$this->_pages = [];
	}

	/**
	 * Loop through comments and count the amount of comments for each page, sorting by their status and age
	 *
	 * @param Blog\CommentCollection $comments
	 *
	 * @return array
	 */
	private function _countCommentsByPage(Blog\CommentCollection $comments)
	{
		if (null === $this->_counts) {
			$pendingCommentCount = [];
			$activeCommentCount  = [];

			foreach ($comments as $comment) {
				if ($comment->getStatus() === Blog\Statuses::PENDING) {
					$this->_incrementCommentCount($comment, $pendingCommentCount);
				} elseif ($comment->getStatus() === Blog\Statuses::APPROVED && $this->_commentFromThisWeek($comment)) {
					$this->_incrementCommentCount($comment, $activeCommentCount);
				}
			}

			$this->_counts = [
				Blog\Statuses::PENDING  => $pendingCommentCount,
				Blog\Statuses::APPROVED => $activeCommentCount,
			];
		}

		return $this->_counts;
	}

	/**
	 * Assign comment to a page comment count
	 *
	 * @param Blog\Comment $comment
	 *
	 * @param array $count
	 */
	private function _incrementCommentCount(Blog\Comment $comment, array &$count)
	{
		if (!array_key_exists($comment->getPageID(), $count)) {
			$count[$comment->getPageID()] = 0;
		}
		if (!in_array($comment->getPageID(), $this->_pageIDs)) {
			$this->_pageIDs[] = $comment->getPageID();
		}

		$count[$comment->getPageID()]++;
	}

	/**
	 * Check to see if a comment was updated within the last seven days
	 *
	 * @param Blog\Comment $comment
	 *
	 * @return bool
	 */
	private function _commentFromThisWeek(Blog\Comment $comment)
	{
		if (null === $this->_aWeekAgo) {
			$this->_aWeekAgo = $this->_getDayTimestamp(new \DateTime('-1 week'));
		}

		return $this->_getDayTimestamp($comment->getUpdatedAt()) >= $this->_aWeekAgo;
	}

	/**
	 * Get timestamp for 00:00 on a \DateTime
	 *
	 * @param \DateTime $datetime
	 *
	 * @return int
	 */
	private function _getDayTimestamp(\DateTime $datetime)
	{
		$datetime->format('Y-m-d');

		return $datetime->getTimestamp();
	}
}