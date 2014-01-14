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
			$type   = $types->get($data['type']);
			$parent = $data['parent'] ? $this->get('cms.page.loader')->getByID((int) $data['parent']) : null;
			$page   = $this->get('cms.page.create')->create($type, $data['title'], $parent);

			// Check that a page was created and redirect to the Edit page in the CMS
			if ($page) {
				$this->addFlash('success', $this->trans('ms.cms.feedback.create.success'));

				return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $page->id));
			}

			$this->addFlash('error', $this->trans('ms.cms.feedback.create.failure'));
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

		$form->add('title', 'text', $this->trans('ms.cms.attributes.title.label'), array(
			'attr' => array(
				'placeholder' => $this->trans('ms.cms.attributes.title.placeholder'),
				'data-help-key' => 'ms.cms.attributes.title.help'
			),
		))
			->val()->maxLength(255);

		$parents = $this->get('cms.page.loader')->getAll();
		$choices = array();
		if ($parents) {
			foreach ($parents as $p) {
				$spaces = str_repeat('--', $p->depth + 1);
				// don't display the option to move it to a page which doesn't allow children
				if (!$p->type->allowChildren()) {
					continue;
				}

				$choices[$p->id] = $spaces.' '.$p->title;
			}
		}

		$form->add('parent', 'choice', $this->trans('ms.cms.attributes.parent.label'), array(
			'choices'     => $choices,
			'empty_value' => $this->trans('Top level'),
			'attr' 		  => array('data-help-key' => 'ms.cms.attributes.parent.help'),
		))->val()
			->optional();

		$form->add('type', 'choice', $this->trans('ms.cms.attributes.type.label'), array(
			'choices'     => $pageTypes,
			'expanded'    => true,
			'empty_value' => false,
			'attr' 		  => array('data-help-key' => 'ms.cms.attributes.type.help'),
		));

		return $form;
	}
}