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
	const CONTENT_ALIAS = 'content_range_filter_pc';

	/**
	 * @var string
	 */
	protected $_field;

	/**
	 * @var string
	 */
	protected $_group;

	/**
	 * @var array
	 */
	protected $_options = [];

	/**
	 * Get an instance of RangeFilterForm, which consists of two drop down menus by default. Can be set to
	 * radio menus by setting the 'expanded' option to `true`.
	 *
	 * {@inheritDoc}
	 *
	 * @return RangeFilterForm
	 */
	public function getForm()
	{
		return new RangeFilterForm;
	}

	/**
	 * {@inheritDoc}
	 */
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

	/**
	 * {@inheritDoc}
	 *
	 * @param $value array
	 * @throws \InvalidArgumentException    Throws an exception if $value is not an array
	 * @throws \LogicException              Throws an exception if there are keys that are not 'min' or 'max' in the array
	 */
	public function setValue($value)
	{
		if (!is_array($value)) {
			throw new \InvalidArgumentException('Value on ContentRangeFilter must be an array');
		}

		if (!array_key_exists(RangeFilterForm::MIN, $value)) {
			$value[RangeFilterForm::MIN] = null;
		} elseif (null !== $value[RangeFilterForm::MIN]) {
			$value[RangeFilterForm::MIN] = ($value[RangeFilterForm::MIN] instanceof \DateTime) ?
				$value[RangeFilterForm::MIN]->getTimestamp() :
				(float)$value[RangeFilterForm::MIN];
		}

		if (!array_key_exists(RangeFilterForm::MAX, $value)) {
			$value[RangeFilterForm::MAX] = null;
		} elseif (null !== $value[RangeFilterForm::MAX]) {
			$value[RangeFilterForm::MAX] = ($value[RangeFilterForm::MAX] instanceof \DateTime) ?
				$value[RangeFilterForm::MAX]->getTimestamp() :
				(float)$value[RangeFilterForm::MAX];
		}

		if (count($value) !== 2) {
			throw new \LogicException('Value array must have only two values, with keys of `' . RangeFilterForm::MIN . '` and `' . RangeFilterForm::MAX . '`');
		}

		$this->_value = $value;
	}

	/**
	 * Will not apply the filter if both values are null.
	 * If the filter is applied and the content field is not set on the page, it will not show up in the results
	 *
	 * {@inheritDoc}
	 */
	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{
		$joinedContent = false;

		foreach ($this->_value as $key => $value) {
			if (false === $joinedContent && null !== $value) {
				$queryBuilder->leftJoin(self::CONTENT_ALIAS, $this->_getJoinStatement(), 'page_content')
					->where(self::CONTENT_ALIAS . '.field_name = ?s', [$this->_field]);
				$joinedContent = true;
			}
			if (null !== $value) {
				if ($key === RangeFilterForm::MIN) {
					$queryBuilder->where(self::CONTENT_ALIAS . '.value_string >= ?s', [$this->_value[RangeFilterForm::MIN]]);
				} elseif ($key === RangeFilterForm::MAX) {
					$queryBuilder->where(self::CONTENT_ALIAS . '.value_string <= ?s', [$this->_value[RangeFilterForm::MAX]]);
				} else {
					throw new \LogicException('Key `' . $key . '` should not exist on value!');
				}
			}
		}
	}

	private function _getJoinStatement()
	{
		return 'page.page_id = ' . self::CONTENT_ALIAS . '.page_id  AND (' . self::CONTENT_ALIAS . '.group_name '
		. ($this->_group ?
			'= \'' . $this->_group . '\'' :
			' IS NULL OR ' . self::CONTENT_ALIAS . '.group_name = \'\''
		) . ')';
	}
}