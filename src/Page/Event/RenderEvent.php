<?php

namespace Message\Mothership\CMS\Page\Event;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Content;

/**
 * Abstract event class for use when CMS pages are rendered on the front-end.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
abstract class RenderEvent extends Event
{
	protected $_content;

	/**
	 * {@inheritdoc}
	 *
	 * @see setPage()
	 *
	 * @param Page    $page    The relevant page for this event
	 * @param Content $content The relevant content for this page
	 */
	public function __construct(Page $page, Content $content)
	{
		parent::__construct($page);

		$this->_content = $content;
	}

	/**
	 * Get the content for this page
	 *
	 * @return Content
	 */
	public function getContent()
	{
		return $this->_content;
	}
}