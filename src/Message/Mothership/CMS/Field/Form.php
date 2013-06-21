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

		if($group->isRepeatable()) {
			$repeatableType = new RepeatableFormType;
			$repeatableType->setForm($groupHandler);

			// See http://symfony.com/doc/current/reference/forms/types/collection.html
			$this->_handler->add($group->getName(), 'collection', $group->getLabel(), array(
				'type'         => $repeatableType,
				'allow_add'    => true,
				'allow_delete' => true,
			));
		} else {
			$this->_handler->add($groupHandler->getForm(), 'form', $group->getLabel());
		}

		
	}
}