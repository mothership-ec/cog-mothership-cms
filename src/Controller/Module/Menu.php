<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Mothership\CMS\Page\Page;
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
	 * Build a menu for pages in a given section.
	 *
	 * If a page is passed as the first argument, the menu always lists children
	 * of that page, even if there are none.
	 *
	 * If no page is passed, the current page is used and the menu lists
	 * children of that page if there are any, otherwise pages at the same
	 * level (siblings) are listed.
	 *
	 * @param int|Page|null $page The Page (or ID for a page) to get a section
	 *                            menu for, if null the current page is used
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function sectionMenu($page = null)
	{
		$loader  = $this->get('cms.page.loader')->includeDeleted(false);
		$current = isset($this->_services['cms.page.current']) ? ('cms.page.current') : null;

		if (!is_null($page)) {
			if (!($page instanceof Page)) {
				$page = $loader->getByID($page);
			}

			$pages = $loader->getChildren($page);
		} else {
			if (!$current) {
				throw new \InvalidArgumentException('For non-CMS frontend requests, a section menu can only be built for a specific page (not the current page)');
			}

			$pages = $loader->getChildren($current) ?: $loader->getSiblings($current, true);
		}

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