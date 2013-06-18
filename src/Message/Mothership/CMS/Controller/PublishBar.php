<?php

namespace Message\Mothership\CMS\Controller;

class PublishBar extends \Message\Cog\Controller\Controller
{
	/**
	 * Load the page object into the publish bar
	 *
	 * @param  int $pageID PageID for the page to show publish options for
	 *
	 * @return view of publish bar
	 */
	public function index($pageID)
	{
		$page        = $this->_services['cms.page.loader']->getByID($pageID);
		$isPublished = (bool) $page->publishDateRange->isInRange();

		return $this->render('Message:Mothership:CMS::publish_bar', array(
			'page'        => $page,
			'isPublished' => $isPublished,
		));
	}
}