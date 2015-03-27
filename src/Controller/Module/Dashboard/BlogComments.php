<?php

namespace Message\Mothership\CMS\Controller\Module\Dashboard;

use Message\Mothership\CMS\Blog;
use Message\Cog\Controller\Controller;

class BlogComments extends Controller
{
	public function index()
	{
		$pending          = $this->get('cms.blog.comment_dashboard_loader')->getPendingComments();
		$recentlyApproved = $this->get('cms.blog.comment_dashboard_loader')->getRecentlyApprovedComments();

		$pages = (count($pending) && count($recentlyApproved)) ? $this->get('cms.blog.comment_dashboard_loader')->getPages() : null;

		return $this->render('Message:Mothership:CMS::modules:dashboard:comments', [
			'pending'          => $pending,
			'recentlyApproved' => $recentlyApproved,
			'pages'            => $pages,
		]);
	}
}