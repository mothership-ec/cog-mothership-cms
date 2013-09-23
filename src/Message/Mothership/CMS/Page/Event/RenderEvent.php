<?php

namespace Message\Mothership\CMS\Page\Event;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Content;

/**
 * Event class for when a CMS page is rendered on the frontend, this allows
 * listeners to send additional parameters to the view.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RenderEvent extends Event
{
	protected $_content;
	protected $_params = array();

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

	/**
	 * Add a parameter to pass to the view for this page.
	 *
	 * @param string $name
	 * @param string $value
	 */
	public function addParameter($name, $value)
	{
		$this->_params[$name] = $value;
	}

	/**
	 * Get all parameters defined on this event
	 *
	 * @return array
	 */
	public function getParameters()
	{
		return $this->_params;
	}
}