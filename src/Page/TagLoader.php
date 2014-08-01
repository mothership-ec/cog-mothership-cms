<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\Field;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\DB\Result as DBResult;
use Message\Cog\DB\Entity\EntityLoaderInterface;
use Message\Cog\Field\Factory;

/**
 * Loads page tags by page.
 * 
 * @author Iris Schaffer <iris@message.co.uk>
 */
class TagLoader implements EntityLoaderInterface
{
	protected $_query;

	/**
	 * Constructor.
	 *
	 * @param DBQuery       $query     The database query instance to use
	 */
	public function __construct(DBQuery $query)
	{
		$this->_query        = $query;
	}

	public function load(Page $page)
	{
		$tags = $this->_query->run('
			SELECT
				tag_name
			FROM
				page_tag
			WHERE
				page_id = ?i
		', $page->id);

		return $tags->flatten();
	}
}