<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\Field;
use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\DB\Entity\EntityLoaderInterface;

/**
 * Loads page tags by page.
 * 
 * @author Iris Schaffer <iris@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class TagLoader implements EntityLoaderInterface
{
	private $_queryBuilderFactory;

	/**
	 * Constructor.
	 *
	 * @param QueryBuilderFactory $queryBuilderFactory        The database query instance to use
	 */
	public function __construct(QueryBuilderFactory $queryBuilderFactory)
	{
		$this->_queryBuilderFactory = $queryBuilderFactory;
	}

	/**
	 * Get all tags for one page
	 *
	 * @param Page $page                    The page whose tags to load
	 * @param bool $includeUnpublished      Include tags for unpublished pages
	 * @param bool $includeDeleted          Include tags for deleted pages
	 *
	 * @return array
	 */
	public function load(Page $page, $includeUnpublished = false, $includeDeleted = false)
	{
		return $this->_getQueryBuilder($includeUnpublished, $includeDeleted)
			->where('page_tag.page_id = ?i', [$page->id])
			->getQuery()
			->run()
			->flatten()
		;
	}

	/**
	 * Get all tags
	 *
	 * @param bool $includeUnpublished      Include tags for unpublished pages
	 * @param bool $includeDeleted          Include tags for deleted pages
	 *
	 * @return array
	 */
	public function getAll($includeUnpublished = false, $includeDeleted = false)
	{
		return $this->_getQueryBuilder($includeUnpublished, $includeDeleted)
			->getQuery()
			->run()
			->flatten()
		;
	}

	/**
	 * Get all tags that belong to child pages of the given page
	 *
	 * @param Page $parent                 The parent page whose children's tags will be loaded
	 * @param bool $includeUnpublished     Include tags for unpublished pages
	 * @param bool $includeDeleted         Include tags for deleted pages
	 *
	 * @return array
	 */
	public function getTagsFromChildren(Page $parent, $includeUnpublished = false, $includeDeleted = false)
	{
		return $this->_getQueryBuilder($includeUnpublished, $includeDeleted)
			->where('page.position_left > ?i', [$parent->left])
			->where('page.position_right < ?i', [$parent->right])
			->getQuery()
			->run()
			->flatten()
		;
	}

	/**
	 * Get the instance of the query builder with the basic query set up
	 *
	 * @param bool $includeUnpublished      Include tags for unpublished pages
	 * @param bool $includeDeleted          Include tags for deleted pages
	 *
	 * @return \Message\Cog\DB\QueryBuilder
	 */
	private function _getQueryBuilder($includeUnpublished = false, $includeDeleted = false)
	{
		$queryBuilder = $this->_queryBuilderFactory->getQueryBuilder()
			->select('tag_name', true)
			->from('page_tag')
			->leftJoin('page', 'page.page_id = page_tag.page_id')
		;

		if (!$includeUnpublished) {
			$queryBuilder->where('page.publish_at <= ?d', [new DateTimeImmutable]);
			$queryBuilder->where('(page.unpublish_at > ?d OR page.unpublish_at IS NULL)', [new DateTimeImmutable]);
		}

		if (!$includeDeleted) {
			$queryBuilder->where('page.deleted_at IS NULL');
		}

		return $queryBuilder;
	}
}