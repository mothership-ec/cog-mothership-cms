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

		return $this->render('Message:Mothership:CMS::modules/menu', array(
			'pages'   => $pages,
			'current' => $current,
		));
	}

	public function childrenMenu($pageID = null)
	{

	}

	/**
	 * Renders a menu for the top level of the page heirarchy.
	 *
	 * Pages are only listed if they are set to be visible in menus; are
	 * currently published and are viewable by the current user.
	 *
	 * The current top-level (root) page is passed to the view as "current".
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function topLevel()
	{
		$loader  = $this->get('cms.page.loader')->includeDeleted(false);
		$auth    = $this->get('cms.page.authorisation');
		$current = $loader->getRoot($this->get('cms.page.current'));
		$pages   = array();

		foreach ($loader->getTopLevel() as $page) {
			if ($page->visibilityMenu && $auth->isPublished($page) && $auth->isViewable($page)) {
				$pages[] = $page;
			}
		}

		return $this->render('Message:Mothership:CMS::modules/menu', array(
			'pages'   => $pages,
			'current' => $current,
		));
	}

	public function tagMenu($tags)
	{
		$tags = (array) $tags;

		if (empty($tags)) {
			// throw exception
		}
	}
}