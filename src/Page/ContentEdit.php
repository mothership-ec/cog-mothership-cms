<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\Field;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;

/**
 * @todo implement Locale
 */
class ContentEdit
{
	protected $_transaction;
	protected $_dispatcher;
	protected $_currentUser;

	protected $_updates = array();

	public function __construct(DB\Transaction $trans, DispatcherInterface $eventDispatcher, UserInterface $user)
	{
		$this->_transaction = $trans;
		$this->_dispatcher  = $eventDispatcher;
		$this->_currentUser = $user;
	}

	public function save(Page $page, Content $content)
	{
		$flattened = $this->flatten($content);

		// Delete repeatable content, so any deleted groups are deleted
		foreach ($content as $key => $part) {
			if ($part instanceof Field\RepeatableContainer) {
				$this->_transaction->add('
					DELETE FROM
						page_content
					WHERE
						page_id    = :id?i
					AND group_name = :group?s
				', array(
					'id'    => $page->id,
					'group' => $part->getName(),
				));
			}
		}

		// Replace the content
		foreach ($flattened as $row) {
			if ($row['value'] == 'none') {
				$this->_transaction->add('
					DELETE FROM
						page_content
					WHERE
						page_id    		= :id?i
					AND locale			= :locale?s
					AND field_name		= :field?s
					AND group_name		= :group?s
					AND data_name		= :dataName?s
				', array(
					'id'        => $page->id,
					'locale'    => 'EN', // temporary
					'field'     => $row['field'],
					'group'     => $row['group'],
					'dataName'  => $row['data_name']
				));

				continue;
			}

			$this->_transaction->add('
				REPLACE INTO
					page_content
				SET
					page_id      = :id?i,
					locale       = \'EN\', # TEMPORARY
					field_name   = :field?s,
					data_name    = :data_name?s,
					group_name   = :group?s,
					sequence     = :sequence?i,
					value_string = :value?s,
					value_int    = :value?i
			', array_merge($row, array('id' => $page->id)));
		}

		return $this->_transaction->commit();
	}

	public function updateContent(array $data, Content $content)
	{
		foreach ($data as $name => $val) {
			$part = $content->$name;

			if (!$part) {
				continue;
			}

			if ($part instanceof Field\RepeatableContainer) {
				// Clear all group instances and re-add them (so they have no values)
				$part->clear();
				while ($part->count() < count($val)) {
					$part->add();
				}

				// Set the values
//				$val = array_values($val);
				foreach ($val as $i => $instance) {
					foreach ($instance as $fieldName => $value) {
						$part->get($i)
							->$fieldName
							->setValue($value);
					}
				}
			}
			elseif ($part instanceof Field\Group) {
				foreach ($val as $fieldName => $fieldValue) {
					$part->$fieldName->setValue($fieldValue);
				}
			}
			else {
				$part->setValue($val);
			}
		}

		return $content;
	}

	public function flatten(Content $content)
	{
		$this->_updates = array();

		foreach ($content as $part) {
			if ($part instanceof Field\RepeatableContainer) {
				foreach ($part as $i => $group) {
					$this->_appendGroup($group, $i);
				}
			}
			elseif ($part instanceof Field\Group) {
				$this->_appendGroup($part);
			}
			else {
				$this->_appendField($part);
			}
		}

		$tmp = $this->_updates;

		$this->_updates = array();

		return $tmp;
	}

	protected function _appendGroup(Field\Group $group, $sequence = null)
	{
		foreach ($group->getFields() as $field) {
			$this->_appendField($field, $group->getName(), $sequence);
		}
	}

	protected function _appendField(Field\BaseField $field, $groupName = null, $sequence = null)
	{
		if (is_array($field->getValue())) {
			foreach ($field->getValue() as $key => $value) {
				$this->_updates[] = array(
					'field'     => $field->getName(),
					'data_name' => $key,
					'value'     => $value,
					'group'     => $groupName,
					'sequence'  => $sequence,
				);
			}
		}
		else {
			$this->_updates[] = array(
				'field'     => $field->getName(),
				'data_name' => null,
				'value'     => $field->getValue(),
				'group'     => $groupName,
				'sequence'  => $sequence,
			);
		}
	}
}