<?php

namespace Message\Mothership\CMS\Controller;

use Message\Mothership\CMS\Field\Form;
use Message\Mothership\CMS\Field\Factory;

class Edit extends \Message\Cog\Controller\Controller
{
	/**
	 * Index for editing, this just redirects to the content edit screen.
	 */
	public function index($pageID)
	{
		return $this->redirectToRoute('ms.cp.cms.edit.content', array(
			'pageID' => $pageID,
		));
	}

	public function updateTitle($pageID)
	{
		if (!$data = $this->get('request')->request->get('edit')) {
			return $this->redirect($this->get('request')->headers->get('referer'));
		}

		$page = $this->get('cms.page.loader')->getByID($pageID);
		$page->title = $data['title'];

		$page = $this->get('cms.page.edit')->save($page);

		return $this->redirectToRoute('ms.cp.cms.edit', array(
			'pageID' => $page->id,
		));
	}

	public function content($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		$factory = new Factory($this->get('validator'), 'page_type.' . $page->type->getName());
		$page->type->setFields($factory);

		$form = $this->get('form.handler');
	
		$contentForm = new Form($factory, $form, $this->_services);
		$contentForm->generateForm();

		if ($form->isValid()) {
			// save to the database
		}

		return $this->render('::edit/content', array(
			'page'         => $page,
			'content_form' => $form,
		));
	}

	public function attributes($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		$form = $this->get('form')
			->setName('attributes')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.cms.edit.attributes.action'));

		return $this->render('::edit/attributes', array(
			'page' => $page,
		));
	}

	public function metadata($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		return $this->render('::edit/metadata', array(
			'page' => $page,
		));
	}
}