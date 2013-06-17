<?php

namespace Message\Mothership\CMS\Controller;

class Edit extends \Message\Cog\Controller\Controller
{
	public function index($pageID)
	{
		$page = $this->_services['cms.page.loader']->getByID($pageID);
		return $this->render('Message:Mothership:CMS::edit', array(
			'page'	=> $page,
			'pageTypes' => $this->_services['cms.page.types']
		));
	}

	public function process($pageID)
	{
		if ($data = $this->get('request')->request->get('edit')) {
			$page = $this->_services['cms.page.loader']->getByID($pageID);
			$page->type = $this->_services['cms.page.types']->get($data['type']);
			$page->title = $data['title'];
			//$page->slug = $data['slug'];
			$page = $this->_services['cms.page.edit']->save($page);
			return $this->redirect($this->generateUrl('ms.cms.edit', array('pageID' => $pageID)));
		}
	}
}