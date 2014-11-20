<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Mothership\CMS\Blog;
use Message\Cog\Controller\Controller;

class Comments extends Controller
{
	public function manageComments($pageID)
	{
		$pageID = (int) $pageID;

		$comments = $this->get('cms.blog.comment_loader')->getByPage($pageID, [
			Blog\Statuses::APPROVED,
			Blog\Statuses::PENDING,
		]);

		$form = $this->createForm($this->get('form.manage_comments'), null, [
			'comments' => $comments,
		]);

		return $this->render('Message:Mothership:CMS::edit:comments', [
			'comments' => $comments,
			'form'     => $form,
			'page'     => $this->get('cms.page.loader')->getByID($pageID),
		]);
	}
}