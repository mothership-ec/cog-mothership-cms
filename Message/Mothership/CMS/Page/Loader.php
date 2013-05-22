<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;
use Message\Cog\ValueObject\DateRange;

/**
 * Responsible for loading page data and returning prepared instances of `Page`.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader
{
	protected $_locale;

	/**
	 * Constructor.
	 *
	 * @param \Locale $locale The locale to use for loading translations
	 */
	public function __construct(/* \Locale */ $locale, $query)
	{
		$this->_locale = $locale;
		$this->_query = $query;
	}

	/**
	 * Get page(s) by ID.
	 *
	 * If an array of page IDs is passed, an array of the prepared `Page`
	 * instances is returned where the keys are the page IDs.
	 *
	 * @param	int|array $pageIDs Page ID or array of page IDs to load
	 *
	 * @return Page|array[Page]   Prepared `Page` instance(s)
	 */
	public function getByID($pageIDs)
	{
		if (!is_array($pageIDs)) {
			return $this->_load($pageIDs);
		}

		$return = array();

		foreach ($pageIDs as $pageID) {
			$return[$pageID] = $this->_load($pageID);
		}

		return $return;
	}

	/**
	 * Get a page by its slug.
	 *
	 * @param	string	$slug		  The slug to check for
	 * @param	boolean $checkHistory True to check through historical slug data
	 *
	 * @return Page|false			  Prepared `Page` instance, or false if not found
	 */
	public function getBySlug($slug, $checkHistory = true)
	{

	}

	/**
	 * Get all pages of a specific type.
	 *
	 * @param	PageTypeInterface $pageType The page type to get pages for
	 *
	 * @return array[Page]					An array of prepared `Page` instances
	 */
	public function getByType(PageTypeInterface $pageType)
	{

	}

	/**
	 * Get the child pages for a page.
	 *
	 * @param	Page   $page The page to find the children for
	 *
	 * @return array[Page]	 An array of prepared `Page` instances
	 */
	public function getChildren(Page $page)
	{

	}

	/**
	 * Get the siblings (pages at the same level in the IA) for a page.
	 *
	 * @param	Page   $page The page to find the siblings for
	 *
	 * @return array[Page]	 An array of prepared `Page` instances
	 */
	public function getSiblings(Page $page)
	{

	}

	protected function _load($pageID)
	{


		// return a prepared page object
		$result = $this->_query->run('
			SELECT
				/* locale, */
				/* authorship, */
				page.page_id AS id,
				page.title AS title,
				page.type AS type,
				page.publish_state AS publishState,
				page.publish_at AS publishAt,
				page.unpublish_at AS unpublishAt,
				page.slug AS slug,
				
				page.position_left AS `left`,
				page.position_right AS `right`,
				page.position_depth AS depth,
				
				page.meta_title AS metaTitle,
				page.meta_description AS metaDescription,
				page.meta_html_head AS metaHtmlHead,
				page.meta_html_foot AS metaHtmlFoot,
				
				page.visibility_search AS visibilitySearch,
				page.visibility_menu AS visibilityMenu,
				page.visibility_aggregator AS visibilityAggregator,
				
				page.password AS password,
				page.access AS access,

				page_access_group.group_id AS accessGroups,

				page.comment_enabled AS commentsEnabled,
				page.comment_access AS commentsAccess,
				page.comment_access AS commentsAccessGroups,
				page.comment_approval AS commentsApproval,
				page.comment_expiry AS commentsExpiry

			FROM
				page
			LEFT JOIN
				page_access_group ON (page_access_group.page_id = page.page_id)
			WHERE
				page.page_id = ?i',
			array($pageID)
		);

		$page = new Page;

		if (count($result)) {
			$page = $result->bind($page);
			$datetime = new \DateTime;

			$from = $datetime->setTimestamp($result[0]->publishAt);
			$to = $datetime->setTimestamp($result[0]->unpublishAt);

			$page->publishDateRange = new DateRange($from, $to);
			var_dump($page); exit;
			return $page;

		}
		
	}
}