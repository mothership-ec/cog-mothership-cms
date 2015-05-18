<?php

namespace Message\Mothership\CMS\Page\Filter;

use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB\QueryBuilderInterface;

class TagFilter extends AbstractFilter
{
	public function setValue($value)
	{
		if (!is_string($value)) {
			throw new \LogicException('Value must be a string');
		}

		$this->_value = $value;
	}

	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{
		$queryBuilder->leftJoin('page_tag', 'page.page_id = page_tag.page_id')
			->where('page_tage.tag_name = ?s', $this->_value)
		;
	}
}