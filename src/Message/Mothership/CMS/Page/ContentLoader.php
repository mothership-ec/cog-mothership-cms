<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\Field;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\DB\Result as DBResult;

/**
 * Page content loader, responsible for loading content for pages and populating
 * `PageContent` instances.
 *
 * @todo implement language stacking (base, then language, then country & language)
 * @todo determine when a group is repeatable by checking the page type, rather
 *       than making assumptions based on the data
 *
 * @todo Rewrite this class following the changes to Fields.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ContentLoader
{
	protected $_query;
	protected $_fieldFactory;

	/**
	 * Constructor.
	 *
	 * @param DBQuery      $query     The database query instance to use
	 * @param FieldFactory $paramname The field factory
	 */
	public function __construct(DBQuery $query, FieldFactory $fieldFactory)
	{
		$this->_query        = $query;
		$this->_fieldFactory = $fieldFactory;
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
			AND language_id = :languageID?s
			AND country_id  = :countryID?sn
			ORDER BY
				group_name DESC, sequence ASC, field_name, data_name
		', array(
			'id'         => $page->id,
			'languageID' => $page->languageID,
			'countryID'  => $page->countryID,
		));

		$content = new Content;
		$groups  = array();

		// Clear the field factory
		$this->_fieldFactory->clear();

		// Set the fields on the field factory
		$page->type->setFields($this->_fieldFactory);

		// Set up the fields on the Content instance
		foreach ($this->_fieldFactory as $name => $field) {
			if ($field instanceof Field\Group && $field->isRepeatable()) {
				$content->$name = new Field\RepeatableContainer;
			}

			$content->$name = $field;
		}

		foreach ($result->collect('group') as $groupName => $rows) {
			foreach ($rows as $row) {
				// If this field is in a group
				if ($groupName) {
					$group = $content->$groupName;

					// Get the right group instance if it's a repeatable group
					if ($group instanceof Field\RepeatableContainer) {
						// Ensure the right number of groups are defined
						while (!$group->get($row->sequence)) {
							$group->add(clone $this->_fieldFactory->{$row->group});
						}

						$group = $group->get($row->sequence);
					}

					// Set the field
					$field = $group->{$row->field};
				}
				// If not, finding the field is easy
				else {
					$field = $content->{$row->field};
				}

				// Skip the field if we can't find it
				if (!isset($field)) {
					continue;
				}

				// Set the values
				if ($field instanceof Field\MultipleValueField) {
					$field->setValue($row->data_name, $row->value);
				}
				else {
					$field->setValue($row->value);
				}
			}
		}

		return $content;
	}
}