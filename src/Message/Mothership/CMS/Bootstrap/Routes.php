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
		$router['ms.cms']->add('ms.cms.edit', '/edit/{pageID}', '::Controller:Edit#index')
			->setRequirement('pageID', '\d+');
		$router['ms.cms']->add('ms.cms.edit.move', '/edit/move/{pageID}/{parent}', '::Controller:Edit#move')
			->setRequirement('pageID', '\d+')
			->setRequirement('parent', '\d+');
		$router['ms.cms']->add('ms.cms.edit.process', '/edit/process/{pageID}', '::Controller:Edit#process')
			->setMethod('POST');
	}
}