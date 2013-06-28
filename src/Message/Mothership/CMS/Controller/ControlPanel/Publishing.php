<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Cog\ValueObject\DateTimeImmutable;
use Message\Cog\ValueObject\DateRange;
use Message\Mothership\CMS\Page\Page;

class Publishing extends \Message\Cog\Controller\Controller
{
	public function renderForm($pageID)
	{
		$page        = $this->get('cms.page.loader')->getByID($pageID);
		$isPublished = $this->get('cms.page.authorisation')->isPublished($page);

		$form = $this->_getForm($page);

		return $this->render('Message:Mothership:CMS::publishing', array(
			'page'        => $page,
			'isPublished' => $isPublished,
			'form'		  => $form,
		));
	}

	public function schedule($pageID)
	{
		$page = $this->_services['cms.page.loader']->getByID($pageID);

		$form = $this->_getForm($page);
		if ($form->isValid() && $data = $form->getFilteredData()) {
			$page->publishDateRange = new DateRange(
				$data['publish_date'] ? new DateTimeImmutable($data['publish_date']->format('c')) : null,
				$data['unpublish_date'] ? new DateTimeImmutable($data['unpublish_date']->format('c')) : null
			);
			$this->get('cms.page.edit')->save($page);
		}

		return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $pageID));
	}

	public function publish($pageID, $force = false)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$hasFuture = $page->publishDateRange->getStart() ? $page->publishDateRange->getStart()->getTimestamp() > time(): false;
		if (!$force && $hasFuture) {
			$this->addFlash('warning', $this->trans('ms.cms.feedback.publish.schedule.warning',
				array(
					'%task%' 		=> 'publish',
					'%taskLink%'	=> '<a href="'.$this->generateUrl('ms.cp.cms.edit.publish.force',array(
						'pageID' => $pageID,
						'force'	 => 1,
					)).'">publish</a>'
				)
			));

			return $this->redirectToReferer();
		}
		$this->get('cms.page.edit')->publish($page);

		return $this->redirectToReferer();
	}

	public function unpublish($pageID, $force = false)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$hasFuture = $page->publishDateRange->getEnd() ? $page->publishDateRange->getEnd()->getTimestamp() > time(): false;
		if (!$force && $hasFuture) {
			$this->addFlash('warning', $this->trans('ms.cms.feedback.publish.schedule.warning',
				array(
					'%task%' 		=> 'unpublish',
					'%taskLink%'	=> '<a href="'.$this->generateUrl('ms.cp.cms.edit.unpublish.force',array(
						'pageID' => $pageID,
						'force'	 => 1,
					)).'">unpublish</a>'
				)
			));

			return $this->redirectToReferer();
		}
		$this->get('cms.page.edit')->unpublish($page);

		return $this->redirectToReferer();
	}

	protected function _getForm(Page $page)
	{
		$form = $this->get('form');
 		$form->setAction($this->generateUrl('ms.cp.cms.edit.publish_scheduling', array('pageID' => $page->id)))
			->setMethod('post')
			->setName('schedule')
			->setDefaultValues(array(
				'publish_date' => $page->publishDateRange->getStart(),
				'unpublish_date' => $page->publishDateRange->getEnd(),
			));;

		$form->add('publish_date', 'datetime', 'on')
			->val()
			->optional();

		$form->add('unpublish_date', 'datetime', 'on')
			->val()
			->optional();

		return $form;
	}
}