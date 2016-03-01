<?php

namespace Message\Mothership\CMS\Page\Filter;

use Message\Mothership\CMS\Page\Page;
use Message\Cog\Filter\AbstractFilter;
use Message\Cog\DB;

/**
 * Class AbstractContentFilter
 * @package Message\Mothership\CMS\Page\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Abstract classes for content filters, includes `setField()` method as well as method for generating a join
 * statement to give to the query builder.
 */
abstract class AbstractContentFilter extends AbstractFilter implements ContentFilterInterface
{
	const DB_ALIAS = 'page_content_filter';

	/**
	 * @var string
	 */
	protected $_field;

	/**
	 * @var string
	 */
	protected $_group;

	/**
	 * @var Page
	 */
	protected $_parent;

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
	 * Set parent page, so that only pages which are children will be loaded
	 * @todo add to ContentFilterInterface for next major version
	 *
	 * @param Page $parent
	 */
	public function setParent(Page $parent)
	{
		$this->_parent = $parent;
	}

	/**
	 * Build a join statement for the query builder based on the group
	 *
	 * @return string
	 */
	protected function _getJoinStatement()
	{
		return 'page.page_id = ' . $this->_getContentAlias() . '.page_id  AND (' . $this->_getContentAlias() . '.group_name '
		. ($this->_group ?
			'= \'' . $this->_group . '\'' :
			' IS NULL OR ' . $this->_getContentAlias() . '.group_name = \'\''
		) . ')';
	}

	/**
	 * Get the alias for the joined content table from the database
	 */
	protected function _getContentAlias()
	{
		return self::DB_ALIAS . '_' . $this->getName();
	}

	/**
	 * @param DB\QueryBuilderInterface $queryBuilder
	 */
	protected function _applyParentFilter(DB\QueryBuilderInterface $queryBuilder)
	{
		if ($queryBuilder instanceof DB\QueryBuilder && $this->_parent) {
			$queryBuilder->where('page.left > ?i', [$this->_parent->left])
				->where('page.right < ?i', [$this->_parent->right])
			;
		} elseif ($this->_parent) {
			throw new \LogicException('Cannot apply parent to content filter unless the query builder is an instance of `\\Message\\Cog\\DB\\QueryBuilder`. The QueryBuilderInterface has been deprecated and should not be used.');
		}
	}
}