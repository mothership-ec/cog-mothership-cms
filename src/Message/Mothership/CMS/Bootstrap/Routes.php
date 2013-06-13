<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.cms']->setPrefix('/cms')->setParent('ms.cp');
		$router['ms.cms']->add('ms.cms.dashboard', '/', '::Controller:Dashboard#index')
			->setFormat('ANY');
		$router['ms.cms']->add('ms.cms.create', '/create', '::Controller:Create#index')
			->setFormat('ANY');
		$router['ms.cms']->add('ms.cms.create.process', '/create/process', '::Controller:Create#process')
			->setMethod('POST');
	}
}