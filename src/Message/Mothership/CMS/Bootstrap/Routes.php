<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.cp.cms']->setPrefix('/cms')->setParent('ms.cp');

		$router['ms.cp.cms']->add('ms.cp.cms.dashboard', '/', '::Controller:Dashboard#index')
			->setFormat('ANY');

		$router['ms.cp.cms']->add('ms.cp.cms.create', '/create', '::Controller:Create#index')
			->setFormat('ANY');

		$router['ms.cp.cms']->add('ms.cp.cms.create.process', '/create', '::Controller:Create#process')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit', '/edit/{pageID}', '::Controller:Edit#index')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.process', '/edit/{pageID}', '::Controller:Edit#process')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish', '/edit/{pageID}/publish/{csrfHash}', '::Controller:Publishing#publish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.unpublish', '/edit/{pageID}/unpublish/{csrfHash}', '::Controller:Publishing#unpublish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish_scheduling', '/edit/{pageID}/scheduling', '::Controller:Publishing#schedule')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');
	}
}