<?php

namespace Message\Mothership\CMS\Page\Event;

use Symfony\Component\HttpFoundation\Response;

/**
 * Event class for overriding the `Response` when rendering a CMS page on the
 * front-end. This is handy if you don't want to output content in the normal
 * way.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SetResponseForRenderEvent extends Event
{
	protected $_response;

	/**
	 * Set the `Response` to return for this request.
	 *
	 * @param Response $response
	 */
	public function setResponse(Response $response)
	{
		$this->_response = $response;
	}

	/**
	 * Get the response, if one has been set.
	 *
	 * @return Response|null
	 */
	public function getResponse()
	{
		return $this->_response;
	}
}