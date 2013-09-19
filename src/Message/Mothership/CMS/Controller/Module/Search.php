<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

/**
 * Search module controllers for the frontend of the website.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Search extends Controller
{
	/**
	 * Render the search form.
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function search()
	{
		$form = $this->get('form')
			->setName(null)
			->setMethod('GET')
			->setAction($this->generateUrl('ms.cms.search'))
			->addOptions(array('csrf_protection' => false));

		$form->add('terms', 'search', $this->trans('ms.cms.search.label'));

		return $this->render('Message:Mothership:CMS::modules/search', array(
			'form' => $form,
		));
	}
}