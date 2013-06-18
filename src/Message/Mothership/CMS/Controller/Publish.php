<?php

namespace Message\Mothership\CMS\Controller;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\DateRange;

class Publish extends \Message\Cog\Controller\Controller
{
	protected $_pageID;

	/**
	 * Handle the form request and decide the action
	 *
	 * @param  int $pageID PageID of the page we are updating
	 *
	 * @return redirect back to edit page for that pageID
	 */
	public function process($pageID)
	{
		$this->_pageID = $pageID;
		// Check the form has been processed
		if ($data = $this->get('request')->request->get('publish')) {
			// Decide what to do with the post data
			if (isset($data['publish'])) {
				// Check for a quick publish / unpublish submission
				$data['publish'] == 'publish' ? $this->_publish() : $this->_unpublish();
			} else {
				// Save publish dates
				$this->_savePubishDates($data);
			}
		}
		return $this->redirectToRoute('ms.cms.edit', array('pageID' => $pageID));
	}

	/**
	 * Use the quick publish on the Edit object
	 *
	 * @return  Page 	updated page object
	 */
	protected function _publish()
	{
		$page = $this->_services['cms.page.loader']->getByID($this->_pageID);

		return $this->_services['cms.page.edit']->publish($page);
	}

	/**
	 * Use the quick unpublish on the Edit object
	 *
	 * @return  Page 	updated page object
	 */
	protected function _unpublish()
	{
		$page = $this->_services['cms.page.loader']->getByID($this->_pageID);

		return $this->_services['cms.page.edit']->unpublish($page);
	}

	/**
	 * Update the page with the publish ranges
	 *
	 * @param  array $data 	post data from form
	 *
	 * @return  Page 		updated page object
	 */
	protected function _savePubishDates($data)
	{
		$page = $this->_services['cms.page.loader']->getByID($this->_pageID);
		$from = $data['publish-date'] ? new DateTimeImmutable($data['publish-date'] .' '. $data['publish-time']) : null;
		$to = $data['unpublish-date'] ? new DateTimeImmutable($data['unpublish-date'] .' '. $data['unpublish-time']) : null;
		$page->publishDateRange = new DateRange($from, $to);

		return $this->_services['cms.page.edit']->save($page);
	}
}