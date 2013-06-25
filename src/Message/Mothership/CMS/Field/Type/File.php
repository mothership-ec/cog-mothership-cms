<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

use Message\Mothership\FileManager\File\Type as FileType;

use Message\Cog\Form\Handler;
use Message\Cog\Filesystem;

/**
 * A field for a file in the file manager database.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class File extends Field
{
	protected $_allowedTypes;

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'file', $this->getLabel(), array(
			'attr'       => array('data-help-key' => $this->_getHelpKeys()),
			'data_class' => 'Message\\Cog\\Filesystem\\File',
		));
	}

	public function setAllowedTypes($types)
	{
		if (!is_array($types)) {
			$types = array($types);
		}

		$this->_allowedTypes = $types;

		return $this;
	}

	public function getValue()
	{
		return new Filesystem\File($this->_value);
	}
}