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

		return $this->render('Message:Mothership:CMS::publishing', array(
			'page'        => $page,
			'isPublished' => $isPublished,
			'form'		  => $this->_getForm($page),
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
		if ($this->_hasContent($pageID)) {
			$page = $this->get('cms.page.loader')->getByID($pageID);
			$hasFuture = $page->publishDateRange->getStart() ? $page->publishDateRange->getStart()->getTimestamp() > time(): false;

			$this->_checkForce($pageID, $force, $hasFuture);
		} else {
			$this->addFlash('error', $this->trans('ms.cms.feedback.publish.content.error'));
		}

		return $this->redirectToReferer();
	}

	public function unpublish($pageID, $force = false)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$hasFuture = $page->publishDateRange->getEnd() ? $page->publishDateRange->getEnd()->getTimestamp() > time(): false;
		$this->_checkForce($pageID, $force, $hasFuture, 'unpublish');

		return $this->redirectToReferer();
	}

	/**
	 * Simplify publishing/unpublishing validation process
	 *
	 * @param int $pageID                   Page id
	 * @param bool $force                   Has the user decided to override the publish/unpublish times?
	 * @param bool $hasFuture               Is the page due to be published in the future?
	 * @param string $action                Publish or unpublish
	 * @throws \InvalidArgumentException    Throws exception if $action is not publish or unpublish
	 *
	 * @return Publishing           Returns $this for chainability
	 */
	protected function _checkForce($pageID, $force, $hasFuture, $action = 'publish')
	{
		if (($action != 'publish') && ($action != 'unpublish')) {
			$action = is_string($action) ? "'" . $action . "'" : gettype($action);
			throw new \InvalidArgumentException('$action must be either \'publish\' or \'unpublish\', ' . $action . ' given');
		}

		if (!$force && $hasFuture) {
			$this->addFlash('warning', $this->trans('ms.cms.feedback.publish.schedule.warning',
				array(
					'%task%' 		=> $this->trans('ms.cms.publish.action.'.$action),
					'%taskLink%'	=> '<a href="'.$this->generateUrl('ms.cp.cms.edit.' . $action . '.force',array(
						'pageID' => $pageID,
						'force'	 => 1,
					)).'">' . $this->trans('ms.cms.publish.action.'.$action) . '</a>'
				)
			));

		} else {
			$this->get('cms.page.edit')->$action($this->get('cms.page.loader')->getByID($pageID));
		}

		return $this;
	}

	protected function _getForm(Page $page)
	{
		$form = $this->get('form');
 		$form->setAction($this->generateUrl('ms.cp.cms.edit.publish_scheduling', array('pageID' => $page->id)))
			->setMethod('post')
			->addOptions(array('attr' => array('id' => 'publish')))
			->setName('schedule')
			->setDefaultValues(array(
				'publish_date' => $page->publishDateRange->getStart(),
				'unpublish_date' => $page->publishDateRange->getEnd(),
			));;

		$form->add('publish_date', 'datetime',  $this->trans('ms.cms.publish.publish-date.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.publish.publish-date.help'),
		))
			->val()
			->optional();

		$form->add('unpublish_date', 'datetime',  $this->trans('ms.cms.publish.unpublish-date.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.publish.unpublish-date.help'),
		))
			->val()
			->optional();

		return $form;
	}

	/**
	 * Check that a page has content other than merely a date
	 *
	 * @param int $pageID
	 * @return bool
	 */
	protected function _hasContent($pageID)
	{
		$fields = $this->get('cms.page.content_loader')
			->load($this->get('cms.page.loader')->getByID($pageID))
			->getIterator();

		$content = count($fields) == 0;

		foreach ($fields as $field) {
			$content = $field->hasContent() && (!$field->getType() != '\\DateTime') ? true : $content;
		}

		return $content;
	}
}