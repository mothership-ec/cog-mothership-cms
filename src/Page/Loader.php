<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageType\PageTypeInterface;
use Message\Mothership\CMS\PageType\Collection as PageTypeCollection;

use Message\User\Group\Collection as UserGroupCollection;
use Message\User\UserInterface;

use Message\Cog\ValueObject\DateRange;
use Message\Cog\ValueObject\Authorship;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\Slug;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\Filter\FilterCollection;
use Message\Cog\Pagination\Pagination;
use Message\Cog\DB\Entity\EntityLoaderCollection;
use Message\Cog\ValueObject\Collection;

/**
 * Responsible for loading page data and returning prepared instances of `Page`.
 *
 * Usage as follows:
 *
 * # Load by pageID
 * $page = $loader->getByID(1); // returns page object
 *
 * # Load deleted page - deleted pages are not loaded by default
 * $page = $loader->getByID(3); // returns false as deleted so do the following
 * $page = $loader->includeDeleted(true)->getByID(3); // this will now return the deleted page object
 *
 * # Load by slug
 * // you can use either a slug object or a string
 * $slug = new Slug('/blog/hello-world');
 * $page = $loader->getBySlug($slug); // returns page object
 *
 * # You can then load that pages siblings, or the children
 * $siblings = $loader->getSiblings($page); // returns array of Page objects
 * $children = $loader->getChildren($page); // returns array of Page objects
 * $children = $loader->includeDeleted(true)->getChildren($page); // returns array of Page objects inc deleted pages
 *
 * # You can also load by type
 * $pages = $loader->getByType(new PageType\Blog); // returns array of page types
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Danny Hannah <danny@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Loader
{
	/**
	 * @var PageTypeCollection
	 */
	protected $_pageTypes;

	/**
	 * @var Authorisation
	 */
	protected $_authorisation;

	/**
	 * @var EntityLoaderCollection
	 */
	protected $_loaders;

	/**
	 * @var Pagination
	 */
	protected $_pagination;

	/**
	 * @var QueryBuilderFactory
	 */
	private $_queryBuilderFactory;

	/**
	 * @var QueryBuilderInterface
	 */
	private $_queryBuilder;

	/**
	 * var to toggle the loading of deleted pages
	 *
	 * (default value: false)
	 *
	 * @var bool
	 */
	protected $_loadDeleted     = false;

	/**
	 * var to toggle the loading of unpublished pages
	 *
	 * @var boolean
	 */
	protected $_loadUnpublished = true;

	/**
	 * @var bool
	 */
	protected $_loadUnviewable  = true;

	/**
	 * The order in which to load pages
	 * 
	 * @var string
	 */
	private $_order = PageOrder::STANDARD;

	/**
	 * @var bool
	 */
	private $_returnAsArray;

	/**
	 * @var FilterCollection
	 */
	private $_filters;

	/**
	 * @var Collection
	 */
	private $_pageCache;

	/**
	 * Constructor
	 *
	 * @param QueryBuilderFactory    $queryBuilderFactory   Database query instance to use
	 * @param PageTypeCollection     $pageTypes             Page types available to the system
	 * @param UserGroupCollection    $groups                User groups available to the system
	 * @param Authorisation  	     $authorisation         Authorisation instance to use
	 * @param UserInterface  	     $user                  The logged in user
	 * @param Searcher               $searcher              The page searcher
	 * @param EntityLoaderCollection $loaders               Entity loaders to allow for lazy loading of pages
	 *                                                      via PageProxy instances
	 */
	public function __construct(
		QueryBuilderFactory $queryBuilderFactory,
		PageTypeCollection $pageTypes,
		UserGroupCollection $groups,
		Authorisation $authorisation,
		UserInterface $user,
		Searcher $searcher,
		EntityLoaderCollection $loaders,
		Collection $pageCache
	) {
		$this->_queryBuilderFactory  = $queryBuilderFactory;
		$this->_pageTypes            = $pageTypes;
		$this->_userGroups           = $groups;
		$this->_authorisation        = $authorisation;
		$this->_user 		         = $user;
		$this->_searcher             = $searcher;
		$this->_loaders              = $loaders;
		$this->_pageCache            = $pageCache;
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
		if (!is_numeric($pageIDs) && empty($pageIDs)) {
			if ($this->_pagination !== null) {
				$this->_pagination->setCountQuery('SELECT 0 as `count`');
			}

			return is_array($pageIDs) ? [] : false;
		}

		if (!is_array($pageIDs)) {
			$pageIDs = [$pageIDs];
			$this->_returnAsArray = false;
		} else {
			$this->_returnAsArray = true;
		}

		$this->_buildQuery();

		$this->_queryBuilder
			->where('page.page_id IN (:pageIDs?ji)', ['pageIDs' => $pageIDs]);

		return $this->_loadPages();
	}

	/**
	 * retrive the homepage by getting the left most and top level node in the
	 * tree that is avaialble and not marked as deleted
	 *
	 * @return Page|false 		Page object of homepage to use
	 */
	public function getHomepage()
	{
		$this->_buildQuery();
		$this->_returnAsArray = false;

		$this->_queryBuilder->where('page.deleted_at IS NULL')
			->where('page.position_depth = 0')
			->orderBy('page.position_left')
			->limit(1)
		;

		return $this->_loadPages();
	}

	public function getParent(Page $page)
	{
		$this->_buildQuery();
		$this->_returnAsArray = false;

		$this->_queryBuilder
			->where('page.position_left < ?i', [$page->left])
			->where('page.position_right >= ?i', [$page->left])
			->where('page.position_depth = ?i - 1', [$page->depth])
		;

		return $this->_loadPages();
	}

	/**
	 * Get the root page for a given page.
	 *
	 * If the given page has a depth of 0, it is the root page and is returned
	 * immediately without any database loading.
	 *
	 * Otherwise, the top-level root page for this page is loaded and returned.
	 *
	 * @param  Page   $page The page to search up from
	 *
	 * @return Page|false   The top-level (root) page
	 */
	public function getRoot(Page $page)
	{
		if (0 === $page->depth) {
			return $page;
		}

		$this->_buildQuery();
		$this->_returnAsArray = false;

		$this->_queryBuilder
			->where('page.position_left < ?i', [$page->left])
			->where('page.position_right >= ?i', [$page->right])
			->where('page.position_depth = ?i', [0])
		;

		return $this->_loadPages();
	}

	/**
	 * Find a page by an ancestral slug
	 *
	 * @param string $slug		Slug to check for
	 * @return Page|false		Prepared `Page` instance, or false if not found
	 */
	public function checkSlugHistory($slug)
	{
		$slug = '/' . ltrim($slug, '/');

		$this->_buildQuery();

		$this->_queryBuilder
			->join('page_slug_history', 'page.page_id = page_slug_history.page_id')
			->where('page_slug_history.slug = ?s', [$slug])
		;

		return $this->_loadPages();
	}

	/**
	 * Get all pages of a specific type.
	 *
	 * @param PageTypeInterface|string $pageType The page type or page type name
	 *                                           to get pages for
	 *
	 * @return array[Page]                       An array of prepared `Page`
	 *                                           instances
	 */
	public function getByType($pageType)
	{
		if ($pageType instanceof PageTypeInterface) {
			$pageType = $pageType->getName();
		}

		$this->_buildQuery();

		$this->_queryBuilder
			->where('page.type = ?s', [$pageType])
		;

		return $this->_loadPages();
	}

	/**
	 * Select a page that has a certain tag
	 *
	 * @param $tag
	 * @return array | bool |Page
	 */
	public function getByTag($tag)
	{
		$this->_buildQuery();

		$this->_queryBuilder
			->join('page_tag', 'page.page_id = page_tag.page_id')
			->where('tag_name = ?s', [$tag])
		;

		return $this->_loadPages();
	}

	/**
	 * Return all files in an array
	 * @return Array|File|false - 	returns either an array of File objects, a
	 * 								single file object or false
	 */
	public function getAll()
	{
		$this->_returnAsArray = true;

		$this->_buildQuery();

		return $this->_loadPages();
	}

	/**
	 * @return array
	 */
	public function getTopLevel()
	{
		return $this->getChildren(null);
	}

	/**
	 * Get the child pages for a page.
	 *
	 * @param Page|null $page The page to find the children for
	 *
	 * @return array[Page]	 An array of prepared `Page` instances
	 */
	public function getChildren(Page $page = null)
	{
		$this->_returnAsArray = true;

		if (!$page) {
			$page = new Page;
			$page->left = 0;
			$page->right = null;
			$page->depth = -1;
		}

		$this->_buildQuery();

		$this->_queryBuilder
			->where('page.position_left > ?i', [$page->left])
			->where('page.position_depth = ?i', [$page->depth + 1])
		;

		if (null !== $page->right) {
			$this->_queryBuilder->where('page.position_right < ?i', [$page->right]);
		}

		return $this->_loadPages();
	}

	/**
	 * Get the siblings (pages at the same level in the IA) for a page.
	 *
	 * @param Page $page                 The page to find the siblings for
	 * @param $includeRequestPage bool   Set as true to include $page in the results
	 *
	 * @return array[Page]	 An array of prepared `Page` instances
	 */
	public function getSiblings(Page $page, $includeRequestPage = false)
	{
		$this->_returnAsArray = true;
		$this->_buildQuery();

		// If the page is in the top level, just load all top level pages, else work out
		// which pages have the same parent first
		if ($page->depth == 0) {
			$this->_queryBuilder
				->where('page.position_depth = ?i', [0])
			;

			if (!$includeRequestPage) {
				$this->_queryBuilder
					->where('page.page_id != ?i', [$page->id])
				;
			}
		} else {
			$parentQuery = $this->_queryBuilderFactory->getQueryBuilder()
				->select(['page_id', 'position_left', 'position_right', 'position_depth'])
				->from('page')
				->where('page.position_left < ?i', [$page->left])
				->where('page.position_right >= ?i', [$page->left])
				->where('page.position_depth = ?i - 1', [$page->depth])
			;

			$onStatement = 'page.position_left > parent.position_left
				AND page.position_right < parent.position_right
				AND page.position_depth = parent.position_depth + 1';

			$this->_queryBuilder
				->join('parent', $onStatement, $parentQuery)
			;

			if (!$includeRequestPage) {
				$this->_queryBuilder->where('page.page_id != ?i', [$page->id]);
			}

		}

		return $this->_loadPages();
	}

	/**
	 * Get a page by its slug.
	 *
	 * @param string  $slug		 	The slug to check for
	 * @param boolean $checkHistory True to check through historical slug data
	 *
	 * @return Page|false			Prepared `Page` instance, or false if not found
	 */
	public function getBySlug($slug, $checkHistory = true)
	{
		if ($slug == '/') {
			return $this->getHomepage();
		}

		// Clean up the slug
		$path 	= trim($slug, '/');
		// Turn it into an array and reverse it.
		$parts 	= array_reverse(explode('/', $path));
		$base 	= array_shift($parts);

		$this->_buildQuery();

		// Loop thorough the parts of the url and build joins in order to get
		// all the parent slugs so we can build the full slug because we do not
		// store the full slug in the db
		for ($i = 2; $i <= count($parts) +1; $i++) {
			$level = 'level' . $i;
			$upperLevel = $i === 2 ? 'page' : 'level' . ($i - 1);
			$this->_queryBuilder
				->join($level, $level . '.position_left < ' . $upperLevel . '.position_left AND ' . $level . '.position_right > ' . $upperLevel . '.position_right', 'page')
				->where($level . '.slug = ?s', [$parts[$i-2]])
			;

			if ($this->_loadDeleted) {
				$this->_queryBuilder->where($level . '.deleted_at IS NULL');
			}
		}

		$this->_queryBuilder
			->where('page.slug = ?s', [$base])
			->where('page.position_depth = ?i', [count($parts)])
		;

		d($this->_queryBuilder->getQueryString());

		$pages = $this->_loadPages();

		if ($pages) {
			return $pages;
		}

		if ($checkHistory && $page = $this->checkSlugHistory($slug)) {
			return $this->_returnAsArray ? [$page] : $page;
		}

		return false;
	}

	/**
	 * Get pages that match a set of terms, ordered by score.
	 *
	 * @param  array  $terms   Terms to search
	 * @param  int    $page    Current page
	 * @param  array  $options Various options
	 *
	 * @return array[Page]
	 */
	public function getBySearchTerms($terms, $page = 1, $options = array())
	{
		if (!empty($options['min_length']) && is_int($options['min_length'])) {
			$this->_searcher->setMinTermLength($options['min_length']);
		}
		elseif (!empty($options['min_length'])) {
			throw new \InvalidArgumentException('`min_length` option must be an integer!');
		}

		$this->_searcher->setTerms($terms);

		$ids = $this->_searcher->getIDs();

		if (empty($ids)) {
			return array();
		}

		$results = $this->getByID($ids);

		// Check authorisation restrictions on pages.
		foreach ($results as $i => $page) {
			if (false === $this->_authorisation->isViewable($page, $this->_user) or
				false === $this->_authorisation->isPublished($page)
			) {
				unset($results[$i]);
			}
		}

		return $this->_searcher->getSorted($results);
	}

	/**
	 * @param Pagination $pagination
	 *
	 * @return Loader
	 */
	public function setPagination(Pagination $pagination)
	{
		$this->_pagination = $pagination;

		return $this;
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
	 * Toggle whether or not to load pages which are unpublshied
	 *
	 * @param  bool $bool true / false as to whether to load unpublished pages
	 *
	 * @return $this       Loader object in order to chain methods
	 */
	public function includeUnpublished($bool)
	{
		$this->_loadUnpublished = $bool;
		return $this;
	}

	/**
	 * set the ordering, use the order constants
	 *
	 * @param string $order ordering
	 *
	 * @return Loader
	 */
	public function orderBy(/* string */ $order)
	{
		$this->_order = $order;

		return $this;
	}

	/**
	 * If no filter collection is set on loader, assign filters to the loader. If a filter collection is already
	 * set, loop through them and add them to the main filter collection
	 *
	 * @param FilterCollection $filters
	 *
	 * @return Loader
	 */
	public function applyFilters(FilterCollection $filters)
	{
		if ($this->_filters instanceof FilterCollection) {
			foreach ($filters as $filter) {
				$this->_filters->add($filter);
			}
		} else {
			$this->setFilters($filters);
		}

		return $this;
	}

	/**
	 * Remove all filters from loader
	 *
	 * @return Loader
	 */
	public function clearFilters()
	{
		$this->_filters = null;

		return $this;
	}

	/**
	 * Set filter
	 *
	 * @param FilterCollection $filters
	 *
	 * @return Loader
	 */
	public function setFilters(FilterCollection $filters)
	{
		$this->_filters = $filters;

		return $this;
	}

	/**
	 * Apply the filters to the query builder and then load the pages
	 *
	 * @param FilterCollection $filters
	 *
	 * @return array
	 */
	public function loadFromFilters(FilterCollection $filters)
	{
		$this->applyFilters($filters);

		return $this->getAll();
	}

	/**
	 * @deprecated use getByID() instead
	 *
	 * @param $pageID
	 *
	 * @return Page | array
	 */
	protected function _load($pageID)
	{
		return $this->getByID($pageID);
	}

	/**
	 * Assign a new instance of QueryBuilder to the loader and build the main query for
	 * loading pages
	 *
	 * @param $replace bool    Set to replace the existing query builder if it exists
	 */
	private function _buildQuery($replace = false)
	{
		if (null === $this->_queryBuilder || $replace === true) {
			$this->_queryBuilder = $this->_queryBuilderFactory
				->getQueryBuilder()
				->select([
					'page.page_id AS id',
					'page.title AS title',
					'page.type AS type',
					'page.publish_at AS publishAt',
					'page.unpublish_at AS unpublishAt',
					'page.created_at AS createdAt',
					'page.created_by AS createdBy',
					'page.updated_at AS updatedAt',
					'page.created_by AS updatedBy',
					'page.deleted_at AS deletedAt',
					'page.deleted_by AS deletedBy',
					'IFNULL(CONCAT((
					SELECT
						CONCAT(\'/\',GROUP_CONCAT(p.slug ORDER BY p.position_depth ASC SEPARATOR \'/\'))
					FROM
						page AS p
					WHERE
						p.position_left < page.position_left
					AND
						p.position_right > page.position_right),\'/\',page.slug),page.slug) AS slug',
					'page.position_left AS `left`',
					'page.position_right AS `right`',
					'page.position_depth AS depth',
					'page.meta_title AS metaTitle',
					'page.meta_description AS metaDescription',
					'page.meta_html_head AS metaHtmlHead',
					'page.meta_html_foot AS metaHtmlFoot',
					'page.visibility_search AS visibilitySearch',
					'page.visibility_menu AS visibilityMenu',
					'page.visibility_aggregator AS visibilityAggregator',
					'page.password AS password',
					'page.access AS access',
					'GROUP_CONCAT(page_access_group.group_name SEPARATOR \',\') AS accessGroups',
				])
				->from('page')
				->leftJoin('page_access_group', 'page_access_group.page_id = page.page_id')
				->groupBy('page.page_id');

			if ($this->_order !== PageOrder::NONE) {
				$this->_queryBuilder->orderBy($this->_getOrderStatement());
			}

			if (!$this->_loadDeleted) {
				$this->_queryBuilder->where('page.deleted_at IS NULL');
			}

			if (!$this->_loadUnpublished) {
				$this->_queryBuilder->where('page.publish_at <= ?d', [new DateTimeImmutable]);
				$this->_queryBuilder->where('(page.unpublish_at > ?d OR page.unpublish_at IS NULL)', [new DateTimeImmutable]);
			}

			if ($this->_filters instanceof FilterCollection) {
				foreach ($this->_filters as $filter) {
					$filter->apply($this->_queryBuilder);
				}
			}
		}
	}

	/**
	 * Run the query set in the query builder
	 *
	 * @throws \LogicException            Throws exception if no query builder is set on the loader
	 *
	 * @return \Message\Cog\DB\Result     Returns database result from query
	 */
	private function _runQuery()
	{
		if (null === $this->_queryBuilder) {
			throw new \LogicException('Query builder not set, run _buildQuery() first!');
		}

		if (null !== $this->_pagination) {
			$this->_pagination->setCountQuery($this->_getCountQuery());
			$this->_pagination->setQuery($this->_queryBuilder->getQueryString());

			$result = $this->_pagination->getCurrentPageResults();
		} else {
			$result = $this->_queryBuilder->getQuery()->run();
		}

		$this->_queryBuilder = null;

		return $result;
	}

	/**
	 * Get query for counting pages to add to the paginator if set
	 *
	 * @return string
	 */
	private function _getCountQuery()
	{
		if (null === $this->_queryBuilder) {
			throw new \LogicException('Cannot set count query on page loader paginator before query has been built');
		}

		return $this->_queryBuilderFactory->getQueryBuilder()
			->select('COUNT(p.id) as `count`')
			->from('p', $this->_queryBuilder)
			->getQueryString()
		;
	}

	/**
	 * Load the results into instances of `Page` and return them.
	 *
	 * @return Page|array[Page] Singular Page object, or array of page objects
	 */
	private function _loadPages()
	{
		$results = $this->_runQuery();

		$pages = $results->bindTo(
			'Message\\Mothership\\CMS\\Page\\PageProxy',
			[$this->_loaders],
			false,
			$this->_pageCache
		);

		foreach ($results as $key => $data) {
			$pages[$key]->visibilitySearch     = (bool) $pages[$key]->visibilitySearch;
			$pages[$key]->visibilityMenu       = (bool) $pages[$key]->visibilityMenu;
			$pages[$key]->visibilityAggregator = (bool) $pages[$key]->visibilityAggregator;

			// Load the DateRange object for publishDateRange
			$pages[$key]->publishDateRange = new DateRange(
				$data->publishAt   ? new DateTimeImmutable(date('c', $data->publishAt))   : null,
				$data->unpublishAt ? new DateTimeImmutable(date('c', $data->unpublishAt)) : null
			);

			// Get the page type
			$pages[$key]->type = $this->_pageTypes->get($data->type);

			if (!isset($pages[$key]->slug) || !($pages[$key]->slug instanceof Slug)) {
				// If the page is the most left page then it is the homepage so
				// we need to override the slug to avoid unnecessary redirects
				if ($this->_getMinPositionLeft() == $data->left) {
					$data->slug = new Slug('/');
				}

			
				// Test if already set from cache before loading
				$pages[$key]->slug = new Slug($data->slug);
			}

			// Test if already set from cache before loading
			if (!isset($pages[$key]->type)) {
				$pages[$key]->type = clone $this->_pageTypes->get($data->type);
			}

			// Load authorship details
			// Test if already set from cache before loading
			if (!isset($pages[$key]->authorship)) {
				$pages[$key]->authorship = new Authorship;
				$pages[$key]->authorship->create(new DateTimeImmutable(date('c',$data->createdAt)), $data->createdBy);

				if ($data->updatedAt) {
					$pages[$key]->authorship->update(new DateTimeImmutable(date('c',$data->updatedAt)), $data->updatedBy);
				}

				if ($data->deletedAt) {
					$pages[$key]->authorship->delete(new DateTimeImmutable(date('c',$data->deletedAt)), $data->deletedBy);
				}
			}

			// If the page is set to inherit it's access then loop through each
			// parent to find the inherited access level.
			// Check to see if this has already been loaded first (from cache)
			if (!isset($pages[$key]->access) || !is_array($pages[$key]->access)) {
				$pages[$key]->accessInherited = false;
				$check = $pages[$key];

				while ($pages[$key]->access < 0) {
					$check = $this->_queryBuilderFactory->getQueryBuilder()
						->select([
							'access',
							'GROUP_CONCAT(page_access_group.group_name SEPARATOR \',\') AS accessGroups'
						])
						->from('page')
						->leftJoin('page_access_group', 'page_access_group.page_id = page.page_id')
						->where('page.position_left < ?i', [$check->left])
						->where('page.position_right >= ?i', [$check->left])
						->where('page.position_depth = ?i - 1', [$check->depth])
						->getQuery()
						->run()
					;

					$check = $check->bindTo('Message\\Mothership\\CMS\\Page\\Page');
					$check = $check[0];

					$pages[$key]->access = $check->access;
					$data->accessGroups = $check->accessGroups;

					$pages[$key]->accessInherited = true;
				}

				// Ensure the page access is at least 0
				$pages[$key]->access = max(0, $pages[$key]->access);

				// Load access groups
				$groups = array_filter(explode(',', $data->accessGroups));
				$pages[$key]->accessGroups = array();
				foreach ($groups as $groupName) {
					if ($group = $this->_userGroups->get(trim($groupName))) {
						$pages[$key]->accessGroups[$group->getName()] = $group;
					}
				}
			}

			if (!$this->_loadUnviewable && $this->_authorisation->isViewable($pages[$key], $this->_user)) {
				unset($pages[$key]);
				continue;
			}
		}

		return count($pages) == 1 && !$this->_returnAsArray ? array_shift($pages) : $pages;
	}

	/**
	 * Get the minimum left position from the nested set
	 *
	 * @return int
	 */
	private function _getMinPositionLeft()
	{
		$queryBuilder = $this->_queryBuilderFactory->getQueryBuilder()
			->select('MIN(`position_left`)')
			->from('page')
		;

		if (!$this->_loadDeleted) {
			$queryBuilder->where('deleted_at IS NULL');
		}

		return $queryBuilder->getQuery()->run()->value();
	}

	/**
	 * Get the order statement based on the order that has been set by the `orderBy()` method
	 *
	 * @return string
	 */
	private function _getOrderStatement()
	{
		switch ($this->_order) {
			case PageOrder::ID:
				return "`page`.`page_id` ASC";
			case PageOrder::ID_REVERSE:
				return "`page`.`page_id` DESC";

			case PageOrder::UPDATED_DATE:
				return "`page`.`updated_at` ASC";
			case PageOrder::UPDATED_DATE_REVERSE:
				return "`page`.`updated_at` DESC";

			case PageOrder::CREATED_DATE:
				return "`page`.`created_at` ASC";
			case PageOrder::CREATED_DATE_REVERSE:
				return "`page`.`created_at` DESC";
				
			case PageOrder::REVERSE:
				return "`page`.`position_left` DESC";
			case PageOrder::STANDARD:
			default:
				return "`page`.`position_left` ASC";
		}
	}
}
