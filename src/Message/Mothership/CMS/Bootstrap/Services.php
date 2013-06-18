<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Mothership\CMS;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($serviceContainer)
	{
		$serviceContainer['cms.page.types'] = $serviceContainer->share(function($c) {
			return new CMS\PageType\Collection;
		});

		$serviceContainer['cms.page.nested_set_helper'] = function($c) {
			$helper = $c['db.nested_set_helper'];

			return $helper->setTable('page', 'page_id', 'position_left', 'position_right', 'position_depth');
		};

		$serviceContainer['cms.page.loader'] = $serviceContainer->share(function($c) {
			return new CMS\Page\Loader('Locale class', $c['db.query']);
		});
		$serviceContainer['cms.page.content_loader'] = $serviceContainer->share(function($c) {
			return new CMS\Page\ContentLoader($c['db.query']);
		});

		$serviceContainer['cms.page.create'] = function($c) {
			return new \Message\Mothership\CMS\Page\Create(
				$c['cms.page.loader'],
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.nested_set_helper'],
				$c['user.current']
			);
		};

		$serviceContainer['cms.page.delete'] = function($c) {
			return new \Message\Mothership\CMS\Page\Delete(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.loader']
			);
		};

		$serviceContainer['cms.page.edit'] = function($c) {
			return new \Message\Mothership\CMS\Page\Edit(
				$c['cms.page.loader'],
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.nested_set_helper'],
				$c['user.current']
			);
		};

	}
}