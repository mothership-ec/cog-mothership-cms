<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;
use Message\Cog\ValueObject\DateRange;
use Message\Cog\DB\Query;
/**
 * Responsible for loading page data and returning prepared instances of `Page`.
 *
 * @author	  Joe Holdcroft <joe@message.co.uk>
 * @author	  Danny Hannah <danny@message.co.uk>
 */
class Loader
{
	protected $_locale;
	protected $_query;

	/**
	 * Constructor.
	 *
	 * @param \Locale $locale The locale to use for loading translations
	 */
	public function __construct(/* \Locale */ $locale, Query $query)
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
	 * @param int|array $pageIDs Page ID or array of page IDs to load
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
	 * @param string  $slug		 The slug to check for
	 * @param boolean $checkHistory True to check through historical slug data
	 *
	 * @return Page|false			  Prepared `Page` instance, or false if not found
	 */
	public function getBySlug($slug, $checkHistory = true)
	{
		$path = trim($slug, '/');
		$parts = array_reverse(explode('/', $path));
		$base	 = array_shift($parts);

		$joins = '';
		$where = '';
		$params = array(
			$base,
			count($parts),
		);

		for ($i = 2; $i <= count($parts) +1; $i++) {
			$joins.= " JOIN page level$i ON (level$i.position_left < level".($i-1).".position_left AND level$i.position_right > level".($i-1).".position_right)";
			$where.= " AND level$i.slug = ?s";
			$params[] = $parts[$i-2];
		}

		$result = $this->_query->run('
			SELECT
				level1.page_id
			FROM
				page level1
			'.$joins.'
			WHERE
				level1.slug = ?s
				AND level1.position_depth = ?i
			'.$where.'',
			$params
		);
		
		// If there is a result then retun a page object
		if (count($result)) {
			return $this->getByID($result[0]->page_id);
		}

		// If no result has been returned at this point and $checkHistory is true
		// then we will check the history to see if it existed in the past
		if ($checkHistory && $page = $this->checkSlugHistory($slug)) {
			return $page;
		}

		return false;
	}
	
	
	/**
	 * Find a page by an ancestral slug
	 * 
	 * @param string $slug		Slug to check for
	 * @return Page|false		Prepared `Page` instance, or false if not found
	 */
	public function checkSlugHistory($slug)
	{
		$result = $this->_query->run('
			SELECT
				page_id
			FROM
				slug_history
			WHERE
				slug = ?s
		', $slug);
		
		return (count($result)) ? $this->getByID($result[0]->page_id) : false;
	}

	/**
	 * Get all pages of a specific type.
	 *
	 * @param PageTypeInterface $pageType The page type to get pages for
	 *
	 * @return array[Page]					An array of prepared `Page` instances
	 */
	public function getByType(PageTypeInterface $pageType)
	{
		$result = $this->_query->run('
			SELECT
				page_id
			FROM
				page
			WHERE
				type = ?s
		', $pageType->getName());
		
		return (count($result)) ? $this->getById($result->flatten()) : false;
		
	}

	/**
	 * Get the child pages for a page.
	 *
	 * @param Page $page The page to find the children for
	 *
	 * @return array[Page]	 An array of prepared `Page` instances
	 */
	public function getChildren(Page $page)
	{
		$result = $this->_query->run('
			SELECT
				page_id
			FROM
				page
			WHERE
				position_left > ?i
			AND
				position_right < ?i
			AND
				position_depth = ?i
		', array(
			$page->left,
			$page->right,
			$page->depth+1,
		));

		return (count($result)) ? $this->getById($result->flatten()) : false;
	}

	/**
	 * Get the siblings (pages at the same level in the IA) for a page.
	 *
	 * @param Page $page The page to find the siblings for
	 *
	 * @return array[Page]	 An array of prepared `Page` instances
	 */
	public function getSiblings(Page $page)
	{
		// We have to do a different query if the depth is zero, as this causes
		// complications and have to change the query a fair bit. This way is simpler.
		if ($page->depth == 0) {
			$result = $this->_query->run('
			    SELECT 
			        page.page_id
			    FROM
			        page
			    WHERE
			    	page.position_depth = 0
				AND 
					page.page_id <> ?i
			', array(
			    $page->id,
			));

		} else {
			// Get the parent, then get the children of that parent based on it's position.
			$result = $this->_query->run('
			    SELECT 
			        children.page_id
			    FROM
			        page AS parent
			    LEFT JOIN page AS children
			        ON (
			        	children.position_left > parent.position_left 
			        	AND children.position_right < parent.position_right  
			        	AND children.position_depth = ?i
			        )
			    
			    WHERE	
			        	parent.position_left < ?i
			        	AND parent.position_right > ?i
			        	AND parent.position_depth = ?i
			AND children.page_id <> ?i
			', array(
			    $page->depth,
			    $page->left,
			    $page->right,
			    $page->depth -1,
			    $page->id,
			));
		}

		return (count($result)) ? $this->getById($result->flatten()) : false;
	}


	/**
	 * Load the given page from the DB and populate the Page object
	 * 
	 * @param int 			$pageID id of the page to load
	 * @return Page|false 	populated Page object or false if not found
	 */
	protected function _load($pageID)
	{
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
			array($pageID));

		$page = new Page;

		if (count($result)) {

			// We can use bind here to populate the Page object
			$page = $result->bind($page);
			
			// Create two DateTime objects for the publishDateRange
			$from = new \DateTime(date('c', $result[0]->publishAt));
			$to = new \DateTime(date('c', $result[0]->unpublishAt));

			// Load the DateRange object for publishDateRange
			$page->publishDateRange = new DateRange($from, $to);

			return $page;

		}

		return false;
	}
}