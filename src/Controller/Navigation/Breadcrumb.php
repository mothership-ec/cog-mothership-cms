<?php

namespace Message\Mothership\CMS\Controller\Navigation;

use Message\Mothership\CMS\Page\Page;
use Message\Cog\Controller\Controller;

class Breadcrumb extends Controller
{
	public function breadcrumb(Page $page)
	{
		$builder = $this->get('cms.page.cookietrail.builder');
		$trail   = $builder->getTrailByPage($page);

		return $this->render('Message:Mothership:CMS::navigation:breadcrumb', [
				'trail'       => $trail,
				'currentPage' => $page,
			]);
	}
}