<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Cog\Controller\Controller;
use Message\Mothership\ControlPanel\Event\Dashboard\DashboardEvent;

class Dashboard extends Controller
{
	public function index()
	{
		$event = $this->get('event.dispatcher')->dispatch(
			'dashboard.cms.content',
			new DashboardEvent
		);

		$pages = $this->get('cms.page.loader')->getAll();

		return $this->render('::dashboard', [
			'pages' => $pages,
			'dashboardReferences' => $event->getReferences()
		]);
	}
}