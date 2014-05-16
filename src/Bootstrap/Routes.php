<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\RoutesInterface;

class Routes implements RoutesInterface
{
	public function registerRoutes($router)
	{
		$router['ms.cms']->setPrefix('/')->setPriority(-500);

		$router['ms.cms']->add('ms.cms.search', '/search', 'Message:Mothership:CMS::Controller:Frontend#searchResults');

		$router['ms.cms']->add('ms.cms.contact.action', '/contact/submit', 'Message:Mothership:CMS::Controller:Module:Contact#contactAction')
			->setMethod('POST');

		$router['ms.cms']->add('ms.cms.frontend', '{slug}', 'Message:Mothership:CMS::Controller:Frontend#renderPage')
			->setRequirement('slug', '[a-z0-9\-\/]+')
			->setDefault('slug', '/');

		$router['ms.cms']->add('ms.cms.broken_link.action', '/broken_link', 'Message:Mothership:CMS::Controller:Module:Form#brokenLinkAction')
			->setMethod('POST');

		$router['ms.cp.cms']->setPrefix('/content')->setParent('ms.cp');

		$router['ms.cp.cms']->add('ms.cp.cms.dashboard', '/', 'Message:Mothership:CMS::Controller:ControlPanel:Dashboard#index')
			->setFormat('ANY');

		$router['ms.cp.cms']->add('ms.cp.cms.create.action', '/create', 'Message:Mothership:CMS::Controller:ControlPanel:Create#process')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.create', '/create', 'Message:Mothership:CMS::Controller:ControlPanel:Create#index')
			->setFormat('ANY');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.action', '/edit/{pageID}', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#updateTitle')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.metadata.action', '/edit/{pageID}/metadata', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#metadataAction')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit', '/edit/{pageID}', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#index')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish', '/edit/{pageID}/publish/{csrfHash}', 'Message:Mothership:CMS::Controller:ControlPanel:Publishing#publish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish.force', '/edit/{pageID}/publish/{csrfHash}/{force}', 'Message:Mothership:CMS::Controller:ControlPanel:Publishing#publish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.unpublish', '/edit/{pageID}/unpublish/{csrfHash}', 'Message:Mothership:CMS::Controller:ControlPanel:Publishing#unpublish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.unpublish.force', '/edit/{pageID}/unpublish/{csrfHash}/{force}', 'Message:Mothership:CMS::Controller:ControlPanel:Publishing#unpublish')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.publish_scheduling', '/edit/{pageID}/scheduling', 'Message:Mothership:CMS::Controller:ControlPanel:Publishing#schedule')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.content.action', '/edit/{pageID}/content', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#contentAction')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.content', '/edit/{pageID}/content', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#content')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.attributes.action', '/edit/{pageID}/attributes', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#attributesAction')
			->setRequirement('pageID', '\d+')
			->setMethod('POST');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.attributes.slug.force', '/edit/{pageID}/attributes/slug/{slug}/{csrfHash}', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#forceSlugAction')
			->setRequirement('pageID', '\d+')
			->setMethod('GET')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.attributes', '/edit/{pageID}/attributes', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#attributes')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.metadata', '/edit/{pageID}/metadata', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#metadata')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.delete', '/delete/{pageID}', 'Message:Mothership:CMS::Controller:ControlPanel:Delete#delete')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.restore', '/restore/{pageID}/{csrfHash}', 'Message:Mothership:CMS::Controller:ControlPanel:Delete#restore')
			->setRequirement('pageID', '\d+')
			->enableCsrf('csrfHash');

		$router['ms.cp.cms']->add('ms.cp.cms.edit.metadata', '/edit/{pageID}/metadata', 'Message:Mothership:CMS::Controller:ControlPanel:Edit#metadata')
			->setRequirement('pageID', '\d+');

		$router['ms.cp.cms']->add('ms.cp.cms.search', '/search', 'Message:Mothership:CMS::Controller:ControlPanel:Search#process')
			->setFormat('GET');
	}
}