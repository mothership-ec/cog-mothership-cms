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
		$form = $this->getForm();

		if (!$form->isValid() || !$data = $form->getFilteredData()) {
			// Add error
			return $this->redirectToReferer();
		}

		$pages = $this->get('cms.page.loader')->getBySearchTerms($data['terms']);

		return $this->render('Message:Mothership:CMS::search-results', array(
			'pages' => $pages,
		));
	}

	public function getForm()
	{
		$defaults = array();
		$search = $this->get('http.request.master')->query->get('search');

		if (isset($search['terms']) && $search['terms']) {
			$defaults = array('terms' => $search['terms']);
		}

		$form = $this->get('form')
			->setName('search')
			->setMethod('GET')
			->setAction($this->generateUrl('ms.cp.cms.search'))
			->addOptions(
				array(
					'csrf_protection' => false,
					'attr' => array(
						'class'=>'search',
					)
			))
			->setDefaultValues($defaults);

		$form->add('terms', 'search', $this->trans('ms.cms.search.label'), array(
			'attr' => array(
				'placeholder' => 'Search content…'
			)
		));

		return $form;
	}

}