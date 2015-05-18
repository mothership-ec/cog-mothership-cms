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

	public function load(Page $page)
	{
		return $this->_getQueryBuilder()
			->where('page_id = ?i', [$page->id])
			->getQuery()
			->run()
			->flatten()
		;
	}

	public function getAll()
	{
		return $this->_getQueryBuilder()
			->getQuery()
			->run()
			->flatten()
		;
	}

	private function _getQueryBuilder()
	{
		return $this->_queryBuilderFactory->getQueryBuilder()
			->select('tag_name')
			->from('page_tag')
		;
	}
}