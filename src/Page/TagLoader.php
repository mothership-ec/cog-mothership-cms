<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\Field;

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
	 * @param Page $page
	 *
	 * @return array
	 */
	public function load(Page $page)
	{
		return $this->_getQueryBuilder()
			->where('page_id = ?i', [$page->id])
			->getQuery()
			->run()
			->flatten()
		;
	}

	/**
	 * Get all tags
	 *
	 * @return array
	 */
	public function getAll()
	{
		return $this->_getQueryBuilder()
			->getQuery()
			->run()
			->flatten()
		;
	}

	/**
	 * Get all tags that belong to child pages of the given page
	 *
	 * @param Page $parent    The parent page whose children's tags will be loaded
	 *
	 * @return array
	 */
	public function getTagsFromChildren(Page $parent)
	{
		return $this->_getQueryBuilder()
			->leftJoin('page', 'page.page_id = page_tag.page_id')
			->where('page.position_left > ?i', [$parent->left])
			->where('page.position_right < ?i', [$parent->right])
			->getQuery()
			->run()
			->flatten()
		;
	}

	/**
	 * @return \Message\Cog\DB\QueryBuilder
	 */
	private function _getQueryBuilder()
	{
		return $this->_queryBuilderFactory->getQueryBuilder()
			->select('tag_name', true)
			->from('page_tag')
		;
	}
}