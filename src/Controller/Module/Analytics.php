<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

class Analytics extends Controller
{
	public function analytics()
	{
		$provider = $this->get('analytics.provider');

		return $this->render($provider->getViewReference(), $provider->getViewParams());
	}
}