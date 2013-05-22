<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($serviceContainer)
	{
		$serviceContainer['cms.content_loader'] = $serviceContainer->share(function($c) {
			return new \Message\Mothership\CMS\Page\ContentLoader($c['db.query']);
		});
	}
}