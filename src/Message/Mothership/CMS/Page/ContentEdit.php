<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\Field;

use Message\User\UserInterface;

use Message\Cog\DB;
use Message\Cog\Event\DispatcherInterface;

class ContentEdit
{
	protected $_query;
	protected $_dispatcher;
	protected $_currentUser;

	protected $_updates = array();

	public function __construct(DB\Query $query, DispatcherInterface $eventDispatcher, UserInterface $user)
	{
		$this->_query       = $query;
		$this->_dispatcher  = $eventDispatcher;
		$this->_currentUser = $user;
	}

	public function save(Page $page, Content $content)
	{
		$flattened = $this->flatten($content);

		var_dump($content, $flattened);exit;

		// REPLACE INTO queries in a transaction? or one big fat one?
	}

	public function updateContent(array $data, Content $content)
	{
		$data = $this->_restructureData($data, $content);

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
				foreach ($val as $i => $instance) {
					foreach ($instance as $fieldName => $value) {
						$part->get($i)->$fieldName->setValue($value);
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

	protected function _restructureData(array $data, Content $content)
	{
		foreach ($data as $name => $val) {
			if ($content->{$name} instanceof Field\RepeatableContainer) {
				$groupInstances = array();

				foreach ($val as $fieldName => $values) {
					foreach ($values as $i => $value) {
						$groupInstances[$i][$fieldName] = $value;
					}
				}

				$data[$name] = $groupInstances;
			}
		}

		return $data;
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













	public function flattenContent(Content $content)
	{
		$flattened = array();

		foreach ($content as $name => $part) {
			if ($part instanceof Field\RepeatableContainer) {
				foreach ($part as $i => $group) {
					$flattened = array_merge($flattened, $this->flattenGroup($group, $i));
				}
			}
			elseif ($part instanceof Field\Group) {
				$flattened = array_merge($flattened, $this->flattenGroup($part));
			}
			else {
				$flattened = array_merge($flattened, $this->flattenField($part));
			}
		}

		var_Dump($flattened);exit;
	}

	public function flattenGroup(Field\Group $group, $sequence = null)
	{
		$flattened[] = array();

		foreach ($group->getFields() as $field) {
			$flattened[] = array_merge($flattened, $this->flattenField($field, $group->getName(), $sequence));
		}

		return $flattened;
	}

	public function flattenField(Field\BaseField $field, $groupName = null, $sequence = null)
	{
		$flattened = array();

		if ($field instanceof Field\Field) {
			$flattened[] = array(
				'field_name' => $field->getName(),
				'value'      => $field->getValue(),
				'group_name' => $groupName,
				'sequence'   => $sequence,
			);
		}
		elseif ($field instanceof Field\MultipleValueField) {
			foreach ($field->getValues() as $key => $value) {
				$flattened[] = array(
					'field_name' => $field->getName(),
					'value'      => $value,
					'group_name' => $groupName,
					'sequence'   => $sequence,
				);
			}
		}

		return $flattened;
	}
}