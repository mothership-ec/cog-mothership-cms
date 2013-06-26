<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.cms']->setPrefix('/');

		$router['ms.cms']->add('ms.cms.frontend', '/{slug}', '::Controller:Frontend#renderPage')
			->setRequirement('slug', '[a-z0-9\-\/]+');


		$router['ms.cp.cms']->setPrefix('/cms')->setParent('ms.cp');

		$router['ms.cp.cms']->add('ms.cp.cms.dashboard', '/', '::Controller:Dashboard#index')
			->setFormat('ANY');

		$router['ms.cp.cms']->add('ms.cp.cms.create.action', '/create', '::Controller:Create#process')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.create', '/create', '::Controller:Create#index')
			->setFormat('ANY');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.action', '/edit/{pageID}', '::Controller:Edit#updateTitle')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit', '/edit/{pageID}', '::Controller:Edit#index')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish', '/edit/{pageID}/publish/{csrfHash}', '::Controller:Publishing#publish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.unpublish', '/edit/{pageID}/unpublish/{csrfHash}', '::Controller:Publishing#unpublish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish_scheduling', '/edit/{pageID}/scheduling', '::Controller:Publishing#schedule')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.content', '/edit/{pageID}/content', '::Controller:Edit#content')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.content.action', '/edit/{pageID}/content', '::Controller:Edit#contentAction')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.attributes', '/edit/{pageID}/attributes', '::Controller:Edit#attributes')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.attributes.action', '/edit/{pageID}/attributes', '::Controller:Edit#attributesAction')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.metadata', '/edit/{pageID}/metadata', '::Controller:Edit#metadata')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.delete', '/delete/{pageID}', '::Controller:Delete#delete')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.restore', '/restore/{pageID}/{csrfHash}', '::Controller:Delete#restore')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');
	}
}