<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($serviceContainer)
	{
		$serviceContainer['cms.page.content_loader'] = $serviceContainer->share(function($c) {
			return new \Message\Mothership\CMS\Page\ContentLoader($c['db.query']);
		});

		$serviceContainer['cms.page.create'] = $serviceContainer->share(function($c) {
			return new \Message\Mothership\CMS\Page\Create($c['db.query'], $c['event.dispatcher']);
		});
	}
}