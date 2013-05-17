<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\PageTypeInterface;

/**
 * Responsible for loading page data and returning prepared instances of `Page`.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Loader
{
	protected $_locale;

	/**
	 * Constructor.
	 *
	 * @param \Locale $locale The locale to use for loading translations
	 */
	public function __construct(\Locale $locale)
	{
		$this->_locale = $locale;
	}

	/**
	 * Get page(s) by ID.
	 *
	 * If an array of page IDs is passed, an array of the prepared `Page`
	 * instances is returned where the keys are the page IDs.
	 *
	 * @param  int|array $pageIDs Page ID or array of page IDs to load
	 *
	 * @return Page|array[Page]   Prepared `Page` instance(s)
	 */
	public function getByID($pageIDs)
	{
		if (!is_array($pageIDs)) {
			return $this->_load($pageIDs);
		}

		$return = array();

		foreach ($pageIDs as $pageID) {
			$return[$pageID] = $this->_load($pageID);
		}

		return $return;
	}

	/**
	 * Get a page by its slug.
	 *
	 * @param  string  $slug         The slug to check for
	 * @param  boolean $checkHistory True to check through historical slug data
	 *
	 * @return Page|false            Prepared `Page` instance, or false if not found
	 */
	public function getBySlug($slug, $checkHistory = true)
	{

	}

	/**
	 * Get all pages of a specific type.
	 *
	 * @param  PageTypeInterface $pageType The page type to get pages for
	 *
	 * @return array[Page]                 An array of prepared `Page` instances
	 */
	public function getByType(PageTypeInterface $pageType)
	{

	}

	/**
	 * Get the child pages for a page.
	 *
	 * @param  Page   $page The page to find the children for
	 *
	 * @return array[Page]  An array of prepared `Page` instances
	 */
	public function getChildren(Page $page)
	{

	}

	/**
	 * Get the siblings (pages at the same level in the IA) for a page.
	 *
	 * @param  Page   $page The page to find the siblings for
	 *
	 * @return array[Page]  An array of prepared `Page` instances
	 */
	public function getSiblings(Page $page)
	{

	}

	protected function _load($pageID)
	{
		// return a prepared page object
	}
}