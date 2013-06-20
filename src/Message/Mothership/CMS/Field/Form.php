<?php

namespace Message\Mothership\CMS\Field;

class Form
{
	protected $_factory;

	public function __construct(Factory $factory, $handler, $services)
	{
		$this->_factory  = $factory;
		$this->_handler  = $handler;
		$this->_services = $services;

		$this->_handler->setValidator($this->_factory->getValidator());
	}

	public function generateForm()
	{
		foreach($this->_factory as $fieldName => $field) {
			if ($field instanceof Group) {
				$this->addGroup($field);
			} else if ($field instanceof Field) {
				$this->addField($field);
			}
		}

		$this->_handler->setValidator($this->_factory->getValidator());
	}

	public function addField($field)
	{
		$field->getFormField($this->_handler);
	}

	public function addGroup($group)
	{
		$groupHandler = $this->_services['form.handler']
			->setName($group->getName())
			->addOptions(array(
				'auto_initialize' => false,
			));

		foreach ($group->getFields() as $fieldName => $field) {
			$field->getFormField($groupHandler);
		}

		$this->_handler->add($groupHandler->getForm(), 'form', $group->getLabel());
	}
}