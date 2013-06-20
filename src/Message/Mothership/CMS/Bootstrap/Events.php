<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\EventsInterface;

use Message\Mothership\ControlPanel\Event\Event as CPEvent;

class Events implements EventsInterface
{
	public function registerEvents($dispatcher)
	{
		$dispatcher->addListener(CPEvent::BUILD_MAIN_MENU, function($event) {
			$event->addItem('ms.cp.cms.dashboard', 'Content', array(
				'ms.cp.cms.create',
			));
		});
	}
}