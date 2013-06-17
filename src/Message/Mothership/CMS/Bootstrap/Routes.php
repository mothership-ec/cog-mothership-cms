<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Mothership\CMS;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($routes)
	{
		$routes['ms.cms']->setPrefix('/test');

		$routes['ms.cms']->add('ms.cms.frontend', '/{slug}', '::Controller:Frontend#renderPage')
			->setRequirement('slug', '[a-z0-9\-]+');
	}
}