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

		$router['ms.cp.cms']->add('ms.cp.cms.dashboard', '/', '::Controller:ControlPanel:Dashboard#index')
			->setFormat('ANY');

		$router['ms.cp.cms']->add('ms.cp.cms.create.action', '/create', '::Controller:ControlPanel:Create#process')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.create', '/create', '::Controller:ControlPanel:Create#index')
			->setFormat('ANY');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.action', '/edit/{pageID}', '::Controller:ControlPanel:Edit#updateTitle')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit', '/edit/{pageID}', '::Controller:ControlPanel:Edit#index')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish', '/edit/{pageID}/publish/{csrfHash}', '::Controller:ControlPanel:Publishing#publish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.unpublish', '/edit/{pageID}/unpublish/{csrfHash}', '::Controller:ControlPanel:Publishing#unpublish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish_scheduling', '/edit/{pageID}/scheduling', '::Controller:ControlPanel:Publishing#schedule')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.content.action', '/edit/{pageID}/content', '::Controller:ControlPanel:Edit#contentAction')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.content', '/edit/{pageID}/content', '::Controller:ControlPanel:Edit#content')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.attributes.action', '/edit/{pageID}/attributes', '::Controller:ControlPanel:Edit#attributesAction')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.attributes.slug.force', '/edit/{pageID}/attributes/slug/{slug}/{csrfHash}', '::Controller:ControlPanel:Edit#forceSlugAction')
			->setRequirement('pageID', '\d+')
			->setMethod('GET')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.attributes', '/edit/{pageID}/attributes', '::Controller:ControlPanel:Edit#attributes')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.metadata', '/edit/{pageID}/metadata', '::Controller:ControlPanel:Edit#metadata')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.delete', '/delete/{pageID}', '::Controller:ControlPanel:Delete#delete')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.restore', '/restore/{pageID}/{csrfHash}', '::Controller:ControlPanel:Delete#restore')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');
	}
}