<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\DateRange;

class Publishing extends \Message\Cog\Controller\Controller
{
	public function renderForm($pageID)
	{
		$page        = $this->get('cms.page.loader')->getByID($pageID);
		$isPublished = $this->get('cms.page.authorisation')->isPublished($page);

		return $this->render('Message:Mothership:CMS::publishing', array(
			'page'        => $page,
			'isPublished' => $isPublished,
		));
	}

	public function schedule($pageID)
	{
		// Send the user back if we don't have the form data we expect
		if (!$data = $this->get('request')->request->get('publish')) {
			return $this->redirectToReferer();
		}

		$page = $this->_services['cms.page.loader']->getByID($pageID);

		$page->publishDateRange = new DateRange(
			$data['publish-date'] ? new DateTimeImmutable($data['publish-date'] .' '. $data['publish-time']) : null,
			$data['unpublish-date'] ? new DateTimeImmutable($data['unpublish-date'] .' '. $data['unpublish-time']) : null
		);

		$this->get('cms.page.edit')->save($page);

		return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $pageID));
	}

	public function publish($pageID)
	{
		if ($this->_hasContent($pageID)) {
			$this->get('cms.page.edit')->publish($this->get('cms.page.loader')->getByID($pageID));
		}
		else {
			$this->addFlash('error', $this->trans('ms.cms.feedback.edit.publish.no_content'));
		}

		return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $pageID));
	}

	public function unpublish($pageID)
	{
		$this->get('cms.page.edit')->unpublish($this->get('cms.page.loader')->getByID($pageID));

		return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $pageID));
	}

	/**
	 * Check that a page has content other than merely a date
	 *
	 * @param int $pageID
	 * @return bool
	 */
	protected function _hasContent($pageID)
	{
		$content = false;

		$fields = $this->get('cms.page.content_loader')
			->load($this->get('cms.page.loader')->getByID($pageID))
			->getIterator();

		foreach($fields as $field) {
			$content = $field->getValue() && (!$field->getValue() instanceof \DateTime) ? true : $content;
		}

		return $content;
	}
}