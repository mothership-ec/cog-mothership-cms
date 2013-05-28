<?php

namespace Message\Mothership\CMS\Event;

use Message\Mothership\CMS\Page\Page;

/**
 * Event class for events relating to CMS pages.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class PageEvent extends Event
{
	const CREATE  = 'cms.page.create';
	const EDIT    = 'cms.page.edit';
	const DELETE  = 'cms.page.delete';
	const RESTORE = 'cms.page.restore';

	protected $_page;

	/**
	 * Constructor.
	 *
	 * @see setPage()
	 *
	 * @param Page $page The relevant page for this event.
	 */
	public function __construct(Page $page)
	{
		$this->setPage($page);
	}

	/**
	 * Get the page set for this event.
	 *
	 * @return Page
	 */
	public function getPage()
	{
		return $this->_page;
	}

	/**
	 * Set the page for this event.
	 *
	 * @param Page $page
	 */
	public function setPage(Page $page)
	{
		$this->_page = $page;
	}
}