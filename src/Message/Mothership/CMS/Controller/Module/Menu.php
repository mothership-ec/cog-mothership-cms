<?php

namespace Message\Mothership\CMS\Controller\Module;

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
		$pages   = ($page->hasChildren()) ? $loader->getSiblings($page, true) : $loader->getChildren($page);
		$pages   = $this->_filterPages($pages);

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
		$pages   = $this->_filterPages($loader->getTopLevel());

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

	/**
	 * Filter out any `Page`s in an array that should not be shown in a menu.
	 *
	 * Pages are filtered out if they shouldn't be visible in menus; are not
	 * published; or are not viewable by the current user.
	 *
	 * @param  array[Page]  $pages Array of pages
	 *
	 * @return array[Page]         Filtered array of pages
	 */
	protected function _filterPages(array $pages)
	{
		$auth   = $this->get('cms.page.authorisation');
		$return = array();

		foreach ($pages as $page) {
			if ($page->visibilityMenu && $auth->isPublished($page) && $auth->isViewable($page)) {
				$return[] = $page;
			}
		}

		return $return;
	}
}