<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Mothership\CMS;

use Message\Cog\Bootstrap\EventsInterface;

use Message\Mothership\ControlPanel\Event\Event as CPEvent;

class Events implements EventsInterface
{
	public function registerEvents($dispatcher)
	{
		$dispatcher->addSubscriber(new CMS\EventListener);
	}
}