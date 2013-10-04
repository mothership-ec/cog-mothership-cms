<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Mothership\CMS\Event\Frontend\BuildPageMenuEvent;
use Message\Mothership\CMS\Events;

use Message\Cog\Controller\Controller;

/**
 * Various helpful menus for the frontend.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 *
 * @todo raise Github issue about the fact the Loader doesn't respect ordering
 */
class Menu extends Controller
{
	/**
	 * Build a menu for pages in the current section. If the current page has
	 * children, then the children are listed. Otherwise, other pages at the
	 * same level are listed.
	 *
	 * @param int|null $pageID The page ID to get a section menu for, if null
	 *                         the current page is used.
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function sectionMenu($pageID = null)
	{
		$loader  = $this->get('cms.page.loader')->includeDeleted(false);
		$current = $this->get('cms.page.current');
		$page    = $pageID ? $loader->getByID($pageID) : $current;
		$pages   = $loader->getChildren($page) ?: $loader->getSiblings($page, true);

		$event = new BuildPageMenuEvent('section');
		$event->addPages($pages);

		$pages = $this->get('event.dispatcher')->dispatch(
			Events::FRONTEND_BUILD_MENU,
			$event
		)->getPages();

		return $this->render('Message:Mothership:CMS::modules/menu', array(
			'pages'   => $pages,
			'current' => $current,
		));
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
		$current = isset($this->_services['cms.page.current']) ? $loader->getRoot($this->get('cms.page.current')) : null;

		$event = new BuildPageMenuEvent('main');
		$event->addPages($loader->getTopLevel());

		$pages = $this->get('event.dispatcher')->dispatch(
			Events::FRONTEND_BUILD_MENU,
			$event
		)->getPages();

		return $this->render('Message:Mothership:CMS::modules/menu', array(
			'pages'   => $pages,
			'current' => $current,
		));
	}
}