<?php

namespace Message\Mothership\CMS\Controller;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\DateRange;

class Publish extends \Message\Cog\Controller\Controller
{
	protected $_pageID;

	public function process($pageID)
	{
		$this->_pageID = $pageID;

		if ($data = $this->get('request')->request->get('publish')) {
			if (isset($data['publish']) && $data['publish'] == 'publish') {
				// Quick publish
				$this->_publish();
			} elseif (isset($data['publish']) && $data['publish'] == 'unpublish') {
				// Quick unpublish
				$this->_unpublish();
			} else {
				// Save publish dates
				$this->_savePubishDates($data);
			}
		}
		var_dump($this->generateUrl('ms.cms.edit', array('pageID' => $pageID))); exit;
		return $this->redirect($this->generateUrl('ms.cms.edit', array('pageID' => $pageID))); exit;
	}

	protected function _publish()
	{
		$page = $this->_services['cms.page.loader']->getByID($this->_pageID);
		$page = $this->_services['cms.page.edit']->publish($page);
	}

	protected function _unpublish()
	{
		$page = $this->_services['cms.page.loader']->getByID($this->_pageID);
		$page = $this->_services['cms.page.edit']->unpublish($page);
	}

	protected function _savePubishDates($data)
	{

		$page = $this->_services['cms.page.loader']->getByID($this->_pageID);
		$from = $data['publish-date'] ? new DateTimeImmutable($data['publish-date'] .' '. $data['publish-time']) : null;
		//var_dump($from); exit;
		$to = $data['unpublish-date'] ? new DateTimeImmutable($data['unpublish-date'] .' '. $data['unpublish-time']) : null;

		$page->publishDateRange = new DateRange($from, $to);
		//var_dump($page->publishDateRange ); exit;
		$page = $this->_services['cms.page.edit']->unpublish($page);
	}
}