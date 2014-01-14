<?php

namespace Message\Mothership\CMS\Event\Frontend;

use Message\Mothership\CMS\Page\Page;

/**
 * Event class for when a menu of CMS pages is being built.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class BuildPageMenuEvent extends \Message\Cog\Event\Event
{
	protected $_name;
	protected $_pages = array();

	/**
	 * Constructor.
	 *
	 * @param string $name Identifier name for the menu (so listeners can check
	 *                     which menu the event correlates to)
	 */
	public function __construct($name)
	{
		$this->_name = (string) $name;
	}

	public function getName()
	{
		return $this->_name;
	}

	/**
	 * Get the pages set on this menu.
	 *
	 * @return array[Page]
	 */
	public function getPages()
	{
		return $this->_pages;
	}

	/**
	 * Add a page to this menu.
	 *
	 * @param Page $page
	 *
	 * @return bool False if the page had already been added to the menu
	 */
	public function addPage(Page $page)
	{
		if (array_key_exists($page->id, $this->_pages)) {
			return false;
		}

		$this->_pages[$page->id] = $page;

		return true;
	}

	/**
	 * Add an array of pages to this menu.
	 *
	 * @param array[Page] $pages
	 */
	public function addPages(array $pages)
	{
		foreach ($pages as $page) {
			$this->addPage($page);
		}
	}

	public function remove($pageID)
	{
		if ($pageID instanceof Page) {
			$pageID = $pageID->id;
		}

		if (!array_key_exists($pageID, $this->_pages)) {
			throw new \InvalidArgumentException(sprintf(
				'Page with ID `%s` cannot be removed from `%s` menu as it has not yet been added',
				$pageID,
				$this->_name
			));
		}

		unset($this->_pages[$pageID]);
	}
}