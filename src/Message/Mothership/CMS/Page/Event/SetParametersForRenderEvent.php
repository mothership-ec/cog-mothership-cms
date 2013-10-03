<?php

namespace Message\Mothership\CMS\Page\Event;

/**
 * Event class for setting additional view parameters when rendering a CMS page
 * on the front-end.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SetParametersForRenderEvent extends RenderEvent
{
	protected $_params = array();

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