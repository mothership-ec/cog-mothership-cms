<?php

namespace Message\Mothership\CMS\Controller;

class PublishBar extends \Message\Cog\Controller\Controller
{
	public function index($pageID)
	{
		$page = $this->_services['cms.page.loader']->getByID($pageID);
		//var_dump($page->publishDateRange->getEnd()); exit;
		$isPublished = (bool) $page->publishDateRange->isInRange();

		return $this->render('Message:Mothership:CMS::publish_bar', array(
			'page' => $page,
			'isPublished' => $isPublished,
		));
	}
}