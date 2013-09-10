<?php

namespace Message\Mothership\CMS\Controller;

use Message\Cog\Controller\Controller;

/**
 * Search controller for pages.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Search extends Controller {

	/**
	 * View the search results based against a set of terms.
	 * 
	 * @return Response
	 */
	public function view()
	{
		// $form = $this->_form();
		// $data = $form->getFilteredData();
		// $termsString = $data['terms'];
		$termsString = $_GET['terms'];

		// Split terms into an array on spaces & commas.
		$terms = preg_split("/[\s,]+/", $termsString);

		// Get the current page, default to first.
		$page = isset($_GET['page']) ? $_GET['page'] : 1;

		// Ignore terms less than this length.
		$minTermLength = $this->get('cfg')->search->minTermLength;

		// Fields to in which to search for the terms.
		$searchFields = $this->get('cfg')->search->searchFields;

		// Modifier for result score for fields.
		// Reformat the array due to issues with yaml formatting array keys.
		$tmp = $this->get('cfg')->search->fieldModifiers;
		$fieldModifiers = array();
		foreach ($tmp as $v) {
			$fieldModifiers[$v[0]] = $v[1];
		}

		// Modifier for the type of page.
		// Reformat the array due to issues with yaml formatting array keys.
		$tmp = $this->get('cfg')->search->pageTypeModifiers;
		$pageTypeModifiers = array();
		foreach ($tmp as $v) {
			$pageTypeModifiers[$v[0]] = $v[1];
		}

		// Results per page.
		$perPage = $this->get('cfg')->search->perPage;

		list($totalCount, $pages) = $this->get('cms.page.loader')->getBySearchTerms($terms, $page, array(
			'minTermLength'     => $minTermLength,
			'searchFields'      => $searchFields,
			'fieldModifiers'    => $fieldModifiers,
			'pageTypeModifiers' => $pageTypeModifiers,
			'perPage'           => $perPage,
		));

		return $this->render('::search:listing', array(
			'termsString' => $termsString,
			'pages' => $pages,
			'pagination' => array(
				'page' => $page,
				'perPage' => $perPage,
				'numPages' => floor($totalCount / $perPage)
			)
		));
	}

	/**
	 * Get the search form.
	 * 
	 * @return [type] [description]
	 */
	public function _form()
	{
		$form = $this->get('form')
					 ->setMethod('GET')
					 ->setAction($this->generateUrl('ms.cms.search'));

		$form->add('terms', 'text', 'Search');

		return $form;
	}

}