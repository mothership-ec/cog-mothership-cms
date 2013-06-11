<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($serviceContainer)
	{
		$serviceContainer['cms.page.nested_set_helper'] = function($c) {
			$helper = $c['db.nested_set_helper'];

			return $helper->setTable('page', 'page_id', 'position_left', 'position_right', 'position_depth');
		};

		$serviceContainer['cms.page.loader'] = $serviceContainer->share(function($c) {
			return new \Message\Mothership\CMS\Page\Loader('Locale class', $c['db.query']);
		});
		$serviceContainer['cms.page.content_loader'] = $serviceContainer->share(function($c) {
			return new \Message\Mothership\CMS\Page\ContentLoader($c['db.query']);
		});

		$serviceContainer['cms.page.create'] = function($c) {
			return new \Message\Mothership\CMS\Page\Create(
				$c['cms.page.loader'],
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.nested_set_helper']
			);
		};

		$serviceContainer['cms.page.delete'] = function($c){
			return new \Message\Mothership\CMS\Page\Delete(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['user.current']
			);
		};
	}
}