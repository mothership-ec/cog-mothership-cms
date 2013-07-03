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
				array('addCmsExtension'),
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

	public function addCmsExtension()
	{
		$loader = $this->_services['templating.engine.php']->getLoader();

		$loader->addTemplatePathPatterns(
			array(
				__DIR__ . '/path/to/form/twig/views',
				__DIR__ . '/path/to/form/php/views',
			)
		);

		$this->_services['templating.engine.php']->setLoader($loader);

		$this->_services['form.factory']->addExtensions(
			array(
				new \Message\Mothership\CMS\Field\FormType\CmsExtension
			)
		);

		var_dump($this->_services['form.factory']); die();

	}
}