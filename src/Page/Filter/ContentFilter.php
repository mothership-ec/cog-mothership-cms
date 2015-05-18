<?php

namespace Message\Mothership\CMS\Page\Filter;

use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\DB\QueryBuilderInterface;

/**
 * Class ContentFilter
 *
 * @author Thomas Marchant <thomas@mothership.ec>
 */
class ContentFilter extends AbstractFilter implements ContentFilterInterface
{
	/**
	 * @var QueryBuilderFactory
	 */
	private $_queryBuilderFactory;

	/**
	 * @var string
	 */
	protected $_field;

	/**
	 * @var string
	 */
	protected $_group;

	public function __construct($name, $displayName, QueryBuilderFactory $queryBuilderFactory)
	{
		parent::__construct($name, $displayName);

		$this->_queryBuilderFactory = $queryBuilderFactory;
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

		$this->_setChoices();

		$this->_field = $field;
	}

	public function setValue($value)
	{
		if ($value instanceof \DateTime) {
			$this->_value = (string) $value->getTimestamp();
		} else {
			$this->_value = (string) $value;
		}
	}

	protected function _applyFilter(QueryBuilderInterface $queryBuilder)
	{
		$queryBuilder->leftJoin('page_content', 'page.page_id = page_content.page_id')
			->where('page_content.field_name = ?s', [$this->_field])
			->where('page_content.value_string = ?s', [$this->_value])
		;

		if ($this->_group) {
			$queryBuilder->where('page_content.group_name = ?s', [$this->_group]);
		}
	}

	private function _setChoices()
	{
		$result = $this->_queryBuilderFactory->getQueryBuilder()
			->select('value_string', true)
			->from('page_content')
			->where('field_name = ?s', [$this->_field])
			->getQuery()
			->run()
			->flatten()
		;

		$choices = [];

		foreach ($result as $value) {
			$choices[$value] = $value;
		}

		$this->setOptions(['choices' => $choices]);
	}
}