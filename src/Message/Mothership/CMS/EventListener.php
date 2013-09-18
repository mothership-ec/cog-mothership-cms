<?php

namespace Message\Mothership\CMS;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent;

use Message\Cog\Event\EventListener as BaseListener;
use Message\Cog\Event\SubscriberInterface;
use Message\Cog\HTTP\RedirectResponse;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

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
			BuildMenuEvent::BUILD_MAIN_MENU => array(
				array('registerMainMenuItems'),
			),
			KernelEvents::EXCEPTION => array(
				array('pageNotFound'),
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
	 * Redirect user to dashboard with error message if they get a NotFoundHttpException
	 *
	 * @param GetResponseForExceptionEvent $event
	 */
	public function pageNotFound(GetResponseForExceptionEvent $event)
	{
		$exception = $event->getException();

		// if ($exception instanceof HttpException && $exception->getStatusCode() == 404 && in_array('ms.cp', $event->getRequest()->get('_route_collections'))) {
		// 	$this->_services['http.session']->getFlashBag()->add('error', $exception->getMessage());
		// 	$event->setResponse(new RedirectResponse(
		// 		$this->_services['routing.generator']->generate('ms.cp.cms.dashboard')
		// 	));
		// };
	}
}