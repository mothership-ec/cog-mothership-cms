<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

/**
 * A field that provides a select menu of pre-defined options.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SelectMenu extends Field
{
	protected $_options;

	public function getFormField($form)
	{
		// i dunno :(
	}

	/**
	 * Set the options available on this select menu.
	 *
	 * @param array $options Array of options
	 *
	 * @return SelectMenu    Returns $this for chainability
	 */
	public function setOptions(array $options)
	{
		$this->_options = $options;

		return $this;
	}
}