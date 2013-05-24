<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;
use Message\Cog\ValueObject\DateRange;
use Message\Cog\ValueObject\Authorship;
use Message\Cog\ValueObject\Slug;
use Message\Cog\DB\Query;

/**
 * Responsible for loading page data and returning prepared instances of `Page`.
 *
 * Useage as follows:
 *
 * # Load by pageID
 * $loader = new Loader($locale, $query);
 * $page = $loader->getByID(1); // returns pageID 1
 * 
 * # Load deleted page - deleted pages are not loaded by default
 * $page = $loader->getByID(3); // returns false as deleted so do the following
 * $page = $loader->includeDeleted(true)->getByID(3); // this will now return the deletd page object
 * 
 * # Load by slug
 * // you can use either a slug object or a string
 * $slug = new Slug('/blog/hello-world);
 * $page = $loader->getBySlug($slug); // returns page object
 *
 * # You can then load that pages siblings, or the children
 * $siblings = $loader->getSiblings($page); // returns array of Page objects
 * $children = $loader->getChildren($page); // returns array of Page objects
 * $children = $loader->includeDeleted(true)->getChildren($page); // returns array of Page objects inc deleted pages
 *
 * # You can also load by type
 * $pages = $loader->getByType(new PageTypeInterface\Blog); // Returns array of page types
 *
 *
 * @author	  Joe Holdcroft <joe@message.co.uk>
 * @author	  Danny Hannah <danny@message.co.uk>
 */
class Loader
{
	protected $_locale;
	protected $_query;
	
	/**
	 * var to toggle the loading of deleted pages
	 * 
	 * (default value: false)
	 * 
	 * @var bool
	 */
	protected $_loadDeleted = false;

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
		
		return array_filter($return);
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
			return $this->getByID($result->page_id);
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
		

		return count($result) ? $this->getByID($result->page_id) : false;
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
		', strtolower($pageType->getName()));
		
		return count($result) ? $this->getById($result->flatten()) : false;
		
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

		return count($result) ? $this->getById($result->flatten()) : false;
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

		return count($result) ? $this->getById($result->flatten()) : false;
	}

	
	/**
	 * Toggle whether or not to load deleted pages
	 * 
	 * @param bool $bool 	true / false as to whether to include deleted items
	 * @return 	$this 		Loader object in order to chain the methods
	 */
	public function includeDeleted($bool)
	{
		$this->_loadDeleted = $bool;
		return $this;
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
				page.page_id AS id,
				page.title AS title,
				page.type AS type,
				page.publish_state AS publishState,
				page.publish_at AS publishAt,
				page.unpublish_at AS unpublishAt,
				page.created_at AS createdAt,
				page.created_by AS createdBy,
				page.updated_at AS updatedAt,
				page.created_by AS updatedBy,
				page.deleted_at AS deletedAt,
				page.deleted_by AS deletedBy,
				CONCAT((
					SELECT
						CONCAT(\'/\',GROUP_CONCAT(p.slug ORDER BY p.position_depth ASC SEPARATOR \'/\'))
					FROM 
						page AS p
					WHERE
						p.position_left < page.position_left
					AND 
						p.position_right > page.position_right),\'/\',page.slug) AS slug,

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

		if (count($result)) {
			
			$page = new Page;
			
			// We can use bind here to populate the Page object
			$page = $result->bind($page);

			$result = $result->first();

			if ($result->deletedAt && !$this->_loadDeleted) {
				return false;
			}

			// Create two DateTime objects for the publishDateRange
			$from = new \DateTime(date('c', $result->publishAt));
			$to = new \DateTime(date('c', $result->unpublishAt));

			// Load the DateRange object for publishDateRange
			$page->publishDateRange = new DateRange($from, $to);
			$page->slug = new Slug($result->slug);
			
			// Load authorship details
			$authorship = new Authorship;
			$authorship->create(new \DateTime(date('c', $result->createdAt)), $result->createdBy);
			if ($result->updatedAt) {
				$authorship->delete(new \DateTime(date('c', $result->updatedAt)), $result->updatedBy);
			}			
			if ($result->deletedAt) {
				$authorship->delete(new \DateTime(date('c', $result->deletedAt)), $result->deletedBy);
			}

			$page->authorship = $authorship;
			
			// Remove unneeded properties from the page object which are loaded from the
			// Db to fill vaious things. This is the neatest way I can think of doing it.
			$blankPage = new Page;
			foreach ($result as $k => $v) {
				if (!property_exists($blankPage, $k)) {
					unset($page->{$k});
				}
			}		

			return $page;

		}

		return false;
	}
}