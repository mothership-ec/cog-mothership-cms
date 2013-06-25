<?php

namespace Message\Mothership\CMS\Field;

/**
 * Represents a page content field.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class Field extends BaseField
{
	/**
	 * Set the value for this field.
	 *
	 * @param mixed $value The field value
	 */
	public function setValue($value)
	{
		$this->_value = $value;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getValue()
	{
		return $this->_value;
	}

	/**
	 * Get the contextual help keys for this field, separated with a colon.
	 *
	 * The first key is the help key for this field type, formatted as:
	 * `ms.cms.field_types.[type].help`.
	 *
	 * The second is the help key for the specific content field, formatted as:
	 * `page.[pageType].[groupNameIfSet].[fieldName].help`
	 *
	 * @return string The contextual help keys separated with a colon.
	 */
	protected function _getHelpKeys()
	{
		$className = strtolower(get_class($this));
		$className = trim(strrchr($className, '\\'), '\\');

		return 'ms.cms.field_types.' . $className . '.help:' . $this->_translationKey . '.help';
	}
}