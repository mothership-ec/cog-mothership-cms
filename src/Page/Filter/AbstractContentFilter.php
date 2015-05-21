<?php

namespace Message\Mothership\CMS\Page\Filter;

use Message\Cog\Filter\AbstractFilter;

/**
 * Class AbstractContentFilter
 * @package Message\Mothership\CMS\Page\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Abstract classes for content filters, includes `setField()` method as well as method for generating a join
 * statement to give to the query builder
 */
abstract class AbstractContentFilter extends AbstractFilter implements ContentFilterInterface
{
	/**
	 * @var string
	 */
	protected $_field;

	/**
	 * @var string
	 */
	protected $_group;

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
	 * Get the alias for the `page_content` table to include in the join statement
	 *
	 * @return string
	 */
	abstract protected function _getContentAlias();
}