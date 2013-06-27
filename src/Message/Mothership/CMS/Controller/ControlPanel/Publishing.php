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

	public function publish($pageID, $force = false)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$hasFuture = $page->publishDateRange->getStart() ? $page->publishDateRange->getStart()->getTimestamp() > time(): false;
		if (!$force && $hasFuture) {
			$this->addFlash(
				'warning', $this->trans(
					'ms.cms.feedback.publish.schedule.warning',
					array(
						'task' 		=> 'publish',
						'taskLink'	=> '<a href="'.$this->generateUrl('ms.cp.cms.edit.publish.force',
							array(
								'pageID' => $pageID
							)).'">publish</a>'
					)
			));
			return $this->redirectToReferrer();
		}

		$this->get('cms.page.edit')->publish($page);

		return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $pageID));
	}

	public function unpublish($pageID)
	{
		$this->get('cms.page.edit')->unpublish($this->get('cms.page.loader')->getByID($pageID));

		return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $pageID));
	}
}