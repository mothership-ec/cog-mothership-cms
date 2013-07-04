<?php

namespace Message\Mothership\CMS;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;

/**
 * Event listener for the Mothership CMS.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class EventListener extends BaseListener implements SubscriberInterface
{
	/**
	 * {@inheritDoc}
	 */
	static public function getSubscribedEvents()
	{
		return array(
			'modules.load.success' => array(
				array('registerGroups'),
			),
			BuildMenuEvent::BUILD_MAIN_MENU => array(
				array('registerMainMenuItems')
			),
		);
	}

	/**
	 * Register items to the main menu of the control panel.
	 *
	 * @param BuildMenuEvent $event The event
	 */
	public function registerMainMenuItems(BuildMenuEvent $event)
	{
		$event->addItem('ms.cp.cms.dashboard', 'Content', array('ms.cp.cms'));
	}

	/**
	 * Register user groups.
	 */
	public function registerGroups()
	{
		$this->_services['user.groups']
			->add(new UserGroup\ContentManager);
	}

}