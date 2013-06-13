<?php

namespace Message\Mothership\CMS\Controller;

use Message\Mothership\CMS\Page\Create AS CreatePage;

class Create extends \Message\Cog\Controller\Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:CMS::create', array(
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