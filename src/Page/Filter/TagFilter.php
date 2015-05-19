<?php

namespace Message\Mothership\CMS\Page\Filter;

use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB\QueryBuilderInterface;

class TagFilter extends AbstractFilter
{
	public function setValue($value)
	{
		if (!is_array($value)) {
			throw new \LogicException('Tag filter value must be an array');
		}

		$this->_value = $value;
	}

	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{
		if (empty($this->_value)) {
			return;
		}

		$queryBuilder->leftJoin('page_tag', 'page_tag.page_id = page.page_id')
			->where('page_tag.tag_name IN (?js)', [$this->_value])
		;
	}
}