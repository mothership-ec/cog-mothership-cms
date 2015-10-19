<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

/**
 * @author Samuel Trangmar-Keates
 *
 * Controller for page analytics functionality
 */
class Analytics extends Controller
{
	/**
	 * Renders the analytics script
	 */
	public function analytics()
	{
		$provider = $this->get('analytics.provider');

		return $this->render($provider->getViewReference(), $provider->getViewParams());
	}
}