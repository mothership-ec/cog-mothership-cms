<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;

class Analytics extends Controller
{
	public function analytics()
	{
		return $this->render('Message:Mothership:CMS::modules:analytics:' . $this->get('cfg')->analytics->provider);
	}
}