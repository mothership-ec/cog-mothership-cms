<?php

namespace Message\Mothership\CMS\Page\Filter;

use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\DB\QueryBuilderInterface;

/**
 * Class ContentFilter
 *
 * @author Thomas Marchant <thomas@mothership.ec>
 *
 * Filter for finding pages with matching content
 */
class ContentFilter extends AbstractContentFilter
{
	/**
	 * @var QueryBuilderFactory
	 */
	private $_queryBuilderFactory;

	/**
	 * @param $name
	 * @param $displayName
	 * @param QueryBuilderFactory $queryBuilderFactory
	 *
	 * Include instance of QueryBuilderFactory to allow choices to be automatically loaded
	 */
	public function __construct($name, $displayName, QueryBuilderFactory $queryBuilderFactory)
	{
		parent::__construct($name, $displayName);

		$this->_queryBuilderFactory = $queryBuilderFactory;
	}

	/**
	 * {@inheritDoc}
	 *
	 * Call `setChoices()` after assigning the field and group
	 */
	public function setField($field, $group = null)
	{
		parent::setField($field, $group);
		$this->_setChoices();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @throws \InvalidArgumentException    Throws exception if value is not an array
	 */
	public function setValue($value)
	{
		if (!is_array($value)) {
			throw new \InvalidArgumentException('Value for ContentFilter must be an array, ' . gettype($value) . ' given');
		}

		array_walk($value, function (&$item) {
			if ($item instanceof \DateTime) {
				$item = $item->getTimestamp();
			}
		});

		$this->_value = $value;
	}

	/**
	 * {@inheritDoc}
	 */
	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{
		$queryBuilder->leftJoin($this->_getContentAlias(), $this->_getJoinStatement(), 'page_content')
			->where($this->_getContentAlias() . '.field_name = ?s', [$this->_field])
			->where($this->_getContentAlias() . '.value_string IN (?js)', [$this->_value]);
	}

	/**
	 * Load content assigned to field
	 */
	private function _setChoices()
	{
		$queryBuilder = $this->_queryBuilderFactory->getQueryBuilder()
			->select('value_string', true)
			->from('page_content')
			->where('field_name = ?s', [$this->_field]);

		if (null !== $this->_group) {
			$queryBuilder->where('group_name = ?s', [$this->_group]);
		} else {
			$queryBuilder->where('(group_name IS NULL OR group_name = \'\')');
		}

		$result = $queryBuilder->getQuery()
			->run()
			->flatten();

		$choices = [];

		foreach ($result as $value) {
			$value = (string)$value;

			if ($value !== '') {
				$choices[$value] = $value;
			}
		}

		$this->setOptions(['choices' => $choices]);
	}
}