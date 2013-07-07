<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

class Create extends \Message\Cog\Controller\Controller
{
	public function index()
	{
		return $this->render('::create', array(
			'form'  => $this->_getForm(),
			'types' => $this->get('cms.page.types'),
		));
	}

	public function process()
	{
		$form  = $this->_getForm();
		$types = $this->get('cms.page.types');

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$type = $types->get($data['type']);
			$page = $this->get('cms.page.create')->create($type, $data['title']);
		}

		return $this->render('::create', array(
			'form'  => $form,
			'types' => $types,
		));
	}

	public function _getForm()
	{
		$pageTypes = array();

		foreach ($this->get('cms.page.types') as $type) {
			$pageTypes[$type->getName()] = $type->getDisplayName();
		}

		$form = $this->get('form')
			->setName('content-create')
			->setAction($this->generateUrl('ms.cp.cms.create.action'))
			->setMethod('post');

		$form->add('title', 'text', $this->trans('ms.cms.attributes.title.label'))
			->val()->maxLength(255);

		$form->add('type', 'choice', $this->trans('ms.cms.attributes.type.label'), array(
			'choices'     => $pageTypes,
			'expanded'    => true,
			'empty_value' => false,
		));

		return $form;
	}
}