<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Mothership\CMS\Exception;

class Delete extends \Message\Cog\Controller\Controller
{
	public function renderForm($pageID)
	{
		$page        = $this->get('cms.page.loader')->getByID($pageID);
		$isDeletable = !!! $this->get('cms.page.loader')->getChildren($page);

		return $this->render('Message:Mothership:CMS::deleting', array(
			'page'        => $page,
			'isDeletable' => $isDeletable,
		));
	}

	public function delete($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		try {
			$this->get('cms.page.delete')->delete($page);

			$this->addFlash('success', $this->trans('ms.cms.feedback.delete.success', array(
				'%pageTitle%'  => $page->title,
				'%restoreUrl%' => $this->generateUrl('ms.cp.cms.restore', array('pageID' => $page->id)),
			)));
		}
		catch (Exception\Exception $e) {
			$this->addFlash('error', $e->getMessage());

			return $this->redirectToReferer();
		}

		return $this->redirectToRoute('ms.cp.cms.dashboard');
	}

	public function restore($pageID)
	{
		$page = $this->get('cms.page.loader')->includeDeleted(true)->getByID($pageID);

		$this->get('cms.page.delete')->restore($page);

		$this->addFlash('success', $this->trans('ms.cms.feedback.restore.success', array(
			'%pageTitle%'  => $page->title,
		)));

		return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $page->id));
	}
}