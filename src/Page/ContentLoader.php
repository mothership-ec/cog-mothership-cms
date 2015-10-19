<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\Field;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\DB\Result as DBResult;
use Message\Cog\DB\Entity\EntityLoaderInterface;
use Message\Cog\Field\Factory;
use Message\Cog\Field\ContentBuilder;

/**
 * Page content loader, responsible for loading content for pages and populating
 * `PageContent` instances.
 *
 * @todo implement language stacking (base, then language, then country & language)
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ContentLoader implements EntityLoaderInterface
{
	protected $_query;
	protected $_fieldFactory;
	protected $_contentBuilder;

	/**
	 * Constructor.
	 *
	 * @param DBQuery       $query     The database query instance to use
	 * @param Field\Factory $paramname The field factory
	 */
	public function __construct(DBQuery $query, Factory $fieldFactory, ContentBuilder $contentBuilder)
	{
		$this->_query          = $query;
		$this->_fieldFactory   = $fieldFactory;
		$this->_contentBuilder = $contentBuilder;
	}

	/**
	 * Load page content from the database and prepare it as an instance of
	 * `Page\Content`.
	 *
	 * @param  Page   $page The page to load the content for
	 *
	 * @return Content      The prepared Content instance
	 */
	public function load(Page $page)
	{
		$result = $this->_query->run('
			SELECT
				field_name   AS `field`,
				value_string AS `value`,
				group_name   AS `group`,
				sequence,
				data_name
			FROM
				page_content
			WHERE
				page_id     = :id?i
			#AND language_id = :languageID?s
			#AND country_id  = :countryID?sn
			ORDER BY
				group_name DESC, sequence ASC, field_name, data_name
		', array(
			'id'         => $page->id,
		));

		// Build the fields
		$this->_fieldFactory->build($page->type);

		return $this->_contentBuilder->buildContent($this->_fieldFactory, $result->collect('group'), new Content);
	}
}