<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

class Search extends \Message\Cog\Controller\Controller
{
	public function index($currentPageID = null)
	{
		$form = $this->getForm();

		return $this->render('Message:Mothership:CMS::modules/search', array(
			'form' => $form,
		));
	}

	public function process()
	{
		$terms = $this->get('request')->query->get('terms');

		$search = $this->get('cms.page.searcher');
		$search->setSearchFields(array('title'));
		$search->setMinTermLength(1);
		$search->setExcerptField('title');
		$search->setTerms($terms);
		$search->setFieldModifiers('title');

		de($search->getIDs());
	}

	public function getForm()
	{
		$form = $this->get('form')
			->setName(null)
			->setMethod('GET')
			->setAction($this->generateUrl('ms.cp.cms.search'))
			->addOptions(
				array(
					'csrf_protection' => false,
					'attr' => array(
						'class'=>'search',
						'placeholder' => 'Search Content...',
					)
			));

		$form->add('terms', 'search', $this->trans('ms.cms.search.label'));

		return $form;
	}

}