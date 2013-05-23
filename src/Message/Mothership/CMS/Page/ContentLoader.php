<?php

namespace Message\Mothership\CMS\Page;

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
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class ContentLoader
{
	protected $_query;

	/**
	 * Constructor.
	 *
	 * @param DBQuery $query The database query instance to use
	 */
	public function __construct(DBQuery $query)
	{
		$this->_query = $query;
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
		$result = $this->_query->run("
			SELECT
				field_name   AS field,
				value_string AS value,
				group_name   AS `group`,
				sequence,
				data_name
			FROM
				page_content
			WHERE
				page_id     = ?i
			AND language_id = ?s
			AND country_id  = ?sn
			ORDER BY
				group_name, sequence, field_name, data_name
		", array($page->id, $page->languageID, $page->countryID));

		$content = new Content;
		$groups  = array();

		$this->_validate($result);

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
					$groups[$groupName][$row->sequence]->{$row->field}->{$row->data_name} = $row->value;
				}
			}
		}

		// Set groups on the content instance
		foreach ($groups as $name => $set) {
			// Sort the groups by sequence
			ksort($set);
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
				$content->{$row->field}->{$row->data_name} = $row->value;
			}
		}

		return $content;
	}

	/**
	 * Validate the results of the content retrieval query.
	 *
	 * This method performs the following checks:
	 *
	 *  * Checks that rows with empty `group` parameters don't have a `sequence`
	 *    parameters set.
	 *  * Checks that all values in a "multiple value" field have a name
	 *    (`data_name` parameter).
	 *  * Check there are no collisions of field names (the field name for
	 *    ungrouped fields, the group name for grouped fields).
	 *
	 * @param DBResult $result The database query result
	 *
	 * @throws \RuntimeException If an ungrouped field has a sequence number
	 * @throws \RuntimeException If there is a field name collision
	 * @throws \RuntimeException If a multi-value field has a value with no name
	 */
	protected function _validate(DBResult $result)
	{
		$ids    = array();
		$multis = array();

		foreach ($result as $row) {
			$key  = $row->group ?: $row->field;
			$uid  = $row->group . '-' . $row->field;
			$type = $row->group ? 'group' : ($row->data_name ? 'multi' : 'single');

			// Check only grouped fields have sequence numbers
			if ($row->sequence && !$row->group) {
				throw new \RuntimeException(sprintf('Ungrouped field `%s` cannot have a sequence number', $row->field));
			}

			// If this is a multi-value field, add to the list of multi-value fields
			if ($row->data_name) {
				$multis[] = $uid;
			}

			// If this field is a multi-value field and has no value name, throw exception
			if (in_array($uid, $multis) && !$row->data_name) {
				throw new \RuntimeException(sprintf('Missing value name for multi-value field `%s`', $row->field));
			}

			// If this identifier has already been used
			if (isset($ids[$key])) {
				// If the field was a normal field, no other row can use this identifier
				if ('single' === $ids[$key]) {
					throw new \RuntimeException(sprintf('Field name collision on `%s`', $row->field));
				}
				// If the field isn't the same type, throw exception (once a
				// group or multi-value field uses an identifier, only that group
				// or multi-value field can keep using it)
				if ($type !== $ids[$key]) {
					throw new \RuntimeException(sprintf('Field/group name collision on `%s`', $row->field));
				}
			}
			else {
				$ids[$key] = $type;
			}
		}
	}
}