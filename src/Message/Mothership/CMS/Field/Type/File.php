<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

use Message\Mothership\FileManager\File\Type as FileType;

use Message\Cog\Form\Handler;
use Message\Cog\Filesystem;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * A field for a file in the file manager database.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class File extends Field implements ContainerAwareInterface
{
	protected $_services;

	protected $_allowedTypes;

	/**
	 * Cast this field to a string.
	 *
	 * This outputs the public path to the file, or a blank string if the file
	 * could not be found.
	 *
	 * @return string Public path to the file
	 */
	public function __toString()
	{
		if ($file = $this->_services['file_manager.file.loader']->getByID((int) $this->_value)) {
			$cogFile = new Filesystem\File($file->url);

			return $cogFile->getPublicUrl();
		}

		return '';
	}

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'ms_file', $this->getLabel(), array(
			'attr'          => array('data-help-key' => $this->_getHelpKeys()),
			'choices'       => $this->_getChoices(),
			'allowed_types' => $this->_allowedTypes ?: false,
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
		return $this->_services['file_manager.file.loader']->getByID((int) $this->_value);
	}

	protected function _getChoices()
	{
		$files   = (array) $this->_services['file_manager.file.loader']->getAll();
		$choices = array();

		foreach ($files as $file) {
			if ($this->_allowedTypes) {
				if (!in_array($file->typeID, $this->_allowedTypes)) {
					continue;
				}
			}

			$choices[$file->id] = $file->name;
		}

		return $choices;
	}
}