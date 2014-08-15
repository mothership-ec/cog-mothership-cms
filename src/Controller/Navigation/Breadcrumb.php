<?php

namespace Message\Mothership\CMS\Controller\Navigation;

use Message\Mothership\CMS\Page\Page;
use Message\Cog\Controller\Controller;

class Breadcrumb extends Controller
{
	public function breadcrumb(Page $page)
	{
		$builder = $this->get('cms.page.breadcrumb.builder');
		$trail   = $builder->getTrailFromPage($page);

		return $this->render('::navigation:breadcrumb', [
				'trail'       => $trail,
				'currentPage' => $page,
			]);
	}
}