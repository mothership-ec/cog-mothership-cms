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

	public function process()
	{
		if ($data = $this->get('request')->request->get('create')) {
			$pageType = $this->_services['cms.page.types']->get($data['type']);
			$page = $this->_services['cms.page.create']->create($pageType,$data['title']);
			return $this->redirect($this->generateUrl('ms.cms.dashboard'));
		}
	}
}