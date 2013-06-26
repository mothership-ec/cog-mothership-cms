<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Mothership\CMS\Exception;

class Delete extends \Message\Cog\Controller\Controller
{
	public function delete($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		try {
			$this->get('cms.page.delete')->delete($page);

			$this->addFlash('success', sprintf(
				'Page `%s` has been deleted. <a href="%s">Restore</a>',
				$page->title,
				$this->generateUrl('ms.cp.cms.restore', array('pageID' => $page->id))
			));
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

		$this->addFlash('success', sprintf(
			'Page `%s` has been restored.',
			$page->title
		));

		return $this->redirectToRoute('ms.cp.cms.edit', array('pageID' => $page->id));
	}
}