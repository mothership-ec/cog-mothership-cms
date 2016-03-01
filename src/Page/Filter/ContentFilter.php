<?php

namespace Message\Mothership\CMS\Page\Filter;

use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB;

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
	 * @var DB\QueryBuilderFactory
	 */
	private $_queryBuilderFactory;

	/**
	 * @param $name
	 * @param $displayName
	 * @param DB\QueryBuilderFactory $queryBuilderFactory
	 *
	 * Include instance of QueryBuilderFactory to allow choices to be automatically loaded
	 */
	public function __construct($name, $displayName, DB\QueryBuilderFactory $queryBuilderFactory = null)
	{
		parent::__construct($name, $displayName);

		$this->_queryBuilderFactory = $queryBuilderFactory;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setField($field, $group = null)
	{
		parent::setField($field, $group);
	}

	/**
	 * {@inheritDoc}
	 *
	 * Method calls `_setChoices()` to generate choices if they are not set
	 */
	public function getForm()
	{
		$this->_setChoices();

		return parent::getForm();
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
	protected function _applyFilter(DB\QueryBuilderInterface $queryBuilder)
	{
		$queryBuilder->leftJoin($this->_getContentAlias(), $this->_getJoinStatement(), 'page_content')
			->where($this->_getContentAlias() . '.field_name = ?s', [$this->_field])
			->where($this->_getContentAlias() . '.value_string IN (?js)', [$this->_value])
		;

		$this->_applyParentFilter($queryBuilder);
	}

	/**
	 * Load choices for form field if not already set, and if there is an instance of QueryBuilderFactory set
	 * against the filter class
	 */
	private function _setChoices()
	{
		if (null === $this->_queryBuilderFactory || !empty($this->getOptions()['choices'])) {
			return;
		}

		$queryBuilder = $this->_queryBuilderFactory->getQueryBuilder()
			->select('page_content.value_string', true)
			->from('page_content')
			->join('page', 'page_content.page_id = page.page_id')
			->where('page_content.field_name = ?s', [$this->_field])
			->where('page.deleted_at IS NULL')
			->where('page.publish_at <= UNIX_TIMESTAMP()')
			->where('(page.unpublish_at IS NULL OR page.unpublish_at > UNIX_TIMESTAMP())')
		;

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