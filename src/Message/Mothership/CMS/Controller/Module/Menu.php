<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

/**
 * Various helpful menus for the frontend.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 *
 * @todo raise Github issue about the fact the Loader doesn't respect ordering
 * @todo raise Github issue about translation loading not working as it requires FUCKING Symfony\Config ARGHHHH
 */
class Menu extends Controller
{
	/**
	 * Build a menu for pages in the current section
	 *
	 * @todo getSiblings doesn't return the given page hmm.
	 * @todo hide all with visibilityMenu set to 0
	 * @todo hide all that are unpublished
	 * @todo hide those that the user can't access? or don't? config?
	 */
	public function sectionMenu($pageID = null)
	{
		$loader  = $this->get('cms.page.loader')->includeDeleted(false);
		$current = $this->get('cms.page.current');
		$page    = $pageID ? $loader->getByID($pageID) : $current;
		$pages   = $loader->getSiblings($page);

		return $this->render('::modules/menu', array(
			'pages'   => $pages,
			'current' => $current,
		));
	}

	public function childrenMenu($pageID = null)
	{

	}

	public function topLevelMenu()
	{

	}

	public function tagMenu($tags)
	{
		$tags = (array) $tags;

		if (empty($tags)) {
			// throw exception
		}
	}
}