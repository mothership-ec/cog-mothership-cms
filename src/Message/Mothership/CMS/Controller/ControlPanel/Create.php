<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

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
		if (!$data = $this->get('request')->request->get('create')) {
			return $this->redirectToReferer();
		}

		$type = $this->get('cms.page.types')->get($data['type']);
		$page = $this->get('cms.page.create')->create($type, $data['title']);

		return $this->redirectToRoute('ms.cp.cms.edit', array(
			'pageID' => $page->id,
		));
	}
}