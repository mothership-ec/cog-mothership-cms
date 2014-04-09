<?php

namespace Message\Mothership\CMS;

use Message\Mothership\CMS\Event\Frontend\BuildPageMenuEvent as FrontendBuildMenuEvent;

use Message\Mothership\ControlPanel\Event\BuildMenuEvent as ControlPanelBuildMenuEvent;
use Message\Mothership\ControlPanel\Event\Dashboard\DashboardIndexEvent;

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
			Events::FRONTEND_BUILD_MENU => array(
				array('filterMenuItems', -9000),
			),
			ControlPanelBuildMenuEvent::BUILD_MAIN_MENU => array(
				array('registerMainMenuItems'),
			),
			KernelEvents::EXCEPTION => array(
				array('pageNotFound'),
			),
			DashboardIndexEvent::DASHBOARD_INDEX => array(
				'buildDashboardIndex',
			),
			'dashboard.user.summary.activities' => array(
				'buildDashboardUserSummary',
			),
		);
	}

	/**
	 * Filter out any `Page`s in an array that should not be shown in a menu.
	 *
	 * Pages are filtered out if they shouldn't be visible in menus; are not
	 * published; or are not viewable by the current user.
	 *
	 * @param FrontendBuildMenuEvent  $events Build menu event
	 */
	public function filterMenuItems(FrontendBuildMenuEvent $event)
	{
		$auth = $this->get('cms.page.authorisation');

		foreach ($event->getPages() as $page) {
			if (!$page->visibilityMenu || !$auth->isPublished($page) || !$auth->isViewable($page)) {
				$event->remove($page);
			}
		}
	}

	/**
	 * Register items to the main menu of the control panel.
	 *
	 * @param ControlPanelBuildMenuEvent $event The event
	 */
	public function registerMainMenuItems(ControlPanelBuildMenuEvent $event)
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

	/**
	 * Add controller references to the dashboard index.
	 *
	 * @param  DashboardIndexEvent $event
	 */
	public function buildDashboardIndex(DashboardIndexEvent $event)
	{
		$event->addReference('Message:Mothership:CMS::Controller:Module:Dashboard:CMSSummary#index');
	}

	/**
	 * Add the user's last edited page into the user summary dashboard block.
	 *
	 * @param  [type] $event
	 */
	public function buildDashboardUserSummary($event)
	{
		$pageID = $this->get('db.query')->run("
			SELECT page_id
			FROM page
			WHERE updated_by = :userID?i
			ORDER BY updated_at DESC
			LIMIT 1
		", [
			'userID' => $event->getUser()->id
		]);

		if (count($pageID)) {
			$page = $this->get('cms.page.loader')->getByID($pageID[0]->page_id);

			$event->addActivity([
				'label' => 'Last edited page',
				'date'  => $page->authorship->updatedAt(),
				'name'  => $page->title,
				'url'   => '',
			]);
		}
	}
}