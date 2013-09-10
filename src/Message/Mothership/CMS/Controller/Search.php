<?php

namespace Message\Mothership\CMS\Controller;

use Message\Cog\Controller\Controller;

class Search extends Controller {

	public function view()
	{
		// $form = $this->_form();
		// $data = $form->getFilteredData();
		// $termsString = $data['terms'];
		$termsString = $_GET['terms'];

		// Split terms into an array on spaces & commas.
		$terms = preg_split("/[\s,]+/", $termsString);

		$query = "";

		// Ignore terms less than this length.
		$minTermLength = 3;

		// Fields to in which to search for the terms.
		$searchFields = array(
			'page.title',
			'page_content.value_string',
		);

		// Modifier for result score for fields.
		$fieldModifiers = array(
			'title' => 5,
			'value_string' => 1,
		);

		// Modifier for the type of page.
		$pageTypeModifiers = array(
			'product' => 10,
			'product_listing' => 10,
			'blog' => 1,
			'home' => 1,
		);

		$pages = $this->get('cms.page.loader')->getBySearchTerms($terms, array(
			'minTermLength'     => $minTermLength,
			'searchFields'      => $searchFields,
			'fieldModifiers'    => $fieldModifiers,
			'pageTypeModifiers' => $pageTypeModifiers,
		));

		return $this->render('::search:listing', array(
			'termsString' => $termsString,
			'pages' => $pages
		));
	}

	public function _form()
	{
		$form = $this->get('form')
					 ->setMethod('GET')
					 ->setAction($this->generateUrl('ms.cms.search'));

		$form->add('terms', 'text', 'Search');

		return $form;
	}

}