<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\DB\Query as DBQuery;

/**
 * Page content loader, responsible for loading content for pages and populating
 * `PageContent` instances.
 *
 * @todo implement language stacking (base, then language, then country & language)
 * @todo determine when a group is repeatable by checking the page type, rather
 *       than making assumptions based on the data
 * @todo update the query to use tokens again when the DB component is fixed!
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ContentLoader
{
	protected $_query;

	public function __construct(DBQuery $query)
	{
		$this->_query = $query;
	}

	public function load(Page $page)
	{
		// $result = $this->_query->run("
		// 	SELECT
		// 		*
		// 	FROM
		// 		page_content
		// 	WHERE
		// 		page_id     = :unit_id?i
		// 	AND language_id = ?s
		// 	AND country_id  = ?sn
		// 	ORDER BY
		// 		`group`, sequence, field, data_name
		// ", array($page->id, $page->languageID, $page->countryID));
		$result = $this->_query->run("
			SELECT
				field_name   AS field,
				value_string AS value,
				`group`,
				sequence,
				data_name
			FROM
				page_content
			WHERE
				page_id     = " . $page->id . "
			AND language_id = '" . $page->languageID . "'
			AND country_id  = '" . $page->countryID . "'
			ORDER BY
				`group`, sequence, field_name, data_name
		");

		$content = new Content;
		$groups  = array();

		// Prepare groups
		foreach ($result->collect('group') as $groupName => $rows) {
			// Skip fields not in a group
			if (!$groupName) {
				continue;
			}

			$groups[$groupName] = array();

			// Loop through fields in this group
			foreach ($rows as $row) {
				// Create the group instance in an array, nested by sequence to
				// support repeatable groups
				if (!isset($groups[$groupName][$row->sequence])) {
					$groups[$groupName][$row->sequence] = new Field\Group;
				}

				// Add the field if it hasn't been added yet
				if (!isset($groups[$groupName][$row->sequence]->{$row->field})) {
					$groups[$groupName][$row->sequence]->{$row->field} = $row->data_name
						? new Field\MultipleValueField
						: new Field\Field($row->value);
				}

				// If the field supports multiple values, set the value
				if ($row->data_name) {
					$groups[$groupName][$row->sequence]->{$row->field}->{$row->data_name} = new Field\Field($row->value);
				}
			}
		}

		// Set groups on the content instance
		foreach ($groups as $name => $set) {
			// TODO: determine whether it's repeatable based on the page type
			$content->$name = count($set) > 1 ? new Field\Repeatable($set) : array_shift($set);
		}

		// Set fields not in a group
		foreach ($result as $row) {
			// Skip fields in a group
			if ($row->group) {
				continue;
			}

			// Set the field if it has not been set yet
			if (!isset($content->{$row->field})) {
				$content->{$row->field} = $row->data_name ? new Field\MultipleValueField : new Field\Field($row->value);
			}

			// If the field supports multiple values, set the value
			if ($row->data_name) {
				// Otherwise, check the field allows multiple values
				if (!$content->{$row->field} instanceof Field\MultipleValueField) {
					throw new RuntimeException(sprintf('Field & group name clash on name `%s`', $row->field));
				}

				$content->{$row->field}->{$row->data_name} = new Field\Field($row->value);
			}
		}

		return $content;
	}
}