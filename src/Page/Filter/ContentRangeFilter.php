<?php

namespace Message\Mothership\CMS\Page\Filter;

use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB\QueryBuilderInterface;
use Message\Mothership\CMS\Form\RangeFilterForm;

/**
 * Class ContentRangeFilter
 * @package Message\Mothership\CMS\Page\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class ContentRangeFilter extends AbstractFilter implements ContentFilterInterface
{
	/**
	 * @var string
	 */
	protected $_field;

	/**
	 * @var string
	 */
	protected $_group;

	public function getForm()
	{
		return new RangeFilterForm;
	}

	public function setField($field, $group = null)
	{
		if (!is_string($field)) {
			throw new \InvalidArgumentException('Field must be a string, ' . gettype($field) . ' given');
		}

		if (null !== $group && !is_string($group)) {
			throw new \InvalidArgumentException('Group must be a string, ' . gettype($group) . ' given');
		} elseif ($group) {
			$this->_group = $group;
		}

		$this->_field = $field;
	}

	public function setValue($value)
	{
		if (!is_array($value)) {
			throw new \InvalidArgumentException('Value must be an array');
		}

		if (!array_key_exists(RangeFilterForm::MIN, $value)) {
			$value[RangeFilterForm::MIN] = null;
		} else {
			$value[RangeFilterForm::MIN] = ($value[RangeFilterForm::MIN] instanceof \DateTime) ?
				$value[RangeFilterForm::MIN]->getTimestamp() :
				(float) $value[RangeFilterForm::MIN];
		}

		if (!array_key_exists(RangeFilterForm::MAX, $value)) {
			$value[RangeFilterForm::MAX] = null;
		} else {
			$value[RangeFilterForm::MAX] = ($value[RangeFilterForm::MAX] instanceof \DateTime) ?
				$value[RangeFilterForm::MAX]->getTimestamp() :
				(float) $value[RangeFilterForm::MAX];
		}

		if (count($value) !== 2) {
			throw new \LogicException('Value array must have only two values, with keys of `' . RangeFilterForm::MIN . '` and `' . RangeFilterForm::MAX . '`');
		}

		$this->_value = $value;
	}

	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{
		$queryBuilder->leftJoin('page_content', 'page.page_id = page_content.page_id')
			->where('page_content.field_name = ?s', [$this->_field])
		;

		if (null !== $this->_value[RangeFilterForm::MIN]) {
			$queryBuilder->where('page_content.value_string >= ?s', [$this->_value[RangeFilterForm::MIN]]);
		}

		if (null !== $this->_value[RangeFilterForm::MAX]) {
			$queryBuilder->where('page_content.value_string <= ?s', [$this->_value[RangeFilterForm::MAX]]);
		}

		if ($this->_group) {
			$queryBuilder->where('page_content.group_name = ?s', [$this->_group]);
		}
	}
}