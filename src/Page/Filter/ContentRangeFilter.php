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
 *
 * Filter for finding pages that exist with content values within a certain range
 */
class ContentRangeFilter extends AbstractContentFilter
{
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
				(float) $value[RangeFilterForm::MIN];
		}

		if (!array_key_exists(RangeFilterForm::MAX, $value)) {
			$value[RangeFilterForm::MAX] = null;
		} elseif (null !== $value[RangeFilterForm::MAX]) {
			$value[RangeFilterForm::MAX] = ($value[RangeFilterForm::MAX] instanceof \DateTime) ?
				$value[RangeFilterForm::MAX]->getTimestamp() :
				(float) $value[RangeFilterForm::MAX];
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
				$queryBuilder->leftJoin($this->_getContentAlias(), $this->_getJoinStatement(), 'page_content')
					->where($this->_getContentAlias() . '.field_name = ?s', [$this->_field]);
				$joinedContent = true;
			}
			if (null !== $value) {
				if ($key === RangeFilterForm::MIN) {
					$queryBuilder->where(
						$this->_castSQLValue($this->_getContentAlias() . '.value_string') . ' >= ' . $this->_castSQLValue('?s'),
						[$this->_value[RangeFilterForm::MIN]]);
				} elseif ($key === RangeFilterForm::MAX) {
					$queryBuilder->where(
						$this->_castSQLValue($this->_getContentAlias() . '.value_string') . ' <= ' . $this->_castSQLValue('?s'),
						[$this->_value[RangeFilterForm::MAX]]);
				} else {
					throw new \LogicException('Key `' . $key . '` should not exist on value!');
				}
			}
		}

		$this->_applyParentFilter($queryBuilder);

	}

	/**
	 * Get MySQL statement that casts value to a float
	 * MySQL has a limit on 65 characters for a decimal value, with a maximum of 30 decimal places. This sets the
	 * maximum values to ensure flexibility
	 *
	 * @param $value
	 *
	 * @return string
	 */
	private function _castSQLValue($value)
	{
		return sprintf('CAST(%s as DECIMAL(65,30))', $value);
	}
}