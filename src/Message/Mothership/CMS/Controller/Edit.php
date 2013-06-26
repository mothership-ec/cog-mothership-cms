<?php

namespace Message\Mothership\CMS\Controller;

class Edit extends \Message\Cog\Controller\Controller
{
	public function index($pageID)
	{
		$page = $this->_services['cms.page.loader']->getByID($pageID);

		return $this->render('Message:Mothership:CMS::edit', array(
			'page'	=> $page,
			'pageTypes' => $this->_services['cms.page.types'],
		));
	}

	public function process($pageID)
	{
		if ($data = $this->get('request')->request->get('edit')) {
			$page = $this->_services['cms.page.loader']->getByID($pageID);
			$page->type = $this->_services['cms.page.types']->get($data['type']);
			$page->title = $data['title'];
			$page = $this->_services['cms.page.edit']->save($page);

			return $this->redirectToRoute('ms.cms.edit', array('pageID' => $pageID));
		}
	}

	public function move($pageID, $parent = false)
	{
		$page = $this->_services['cms.page.loader']->getByID($pageID);
		$parent = $this->_services['cms.page.loader']->getByID($parent);
		if ($page->right < $parent->right) {
			var_dump($this->_services['cms.page.edit']->moveNodeRight($page->id, $parent->id));
			exit;
		} else {
			var_dump($this->_services['cms.page.edit']->moveNodeleft($page->id, $parent->id));
			exit;
		}

	}
}