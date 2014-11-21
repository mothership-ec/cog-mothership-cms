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

	public function manageCommentsAction($pageID)
	{
		$pageID = (int) $pageID;

		$comments = $this->get('cms.blog.comment_loader')->getByPage($pageID, [
			Blog\Statuses::APPROVED,
			Blog\Statuses::PENDING,
		]);

		$form = $this->createForm($this->get('form.manage_comments'), null, [
			'comments' => $comments,
		]);
		$form->handleRequest();

		$data = $form->getData();
		if ($form->isValid()) {
			$changed = [];
			foreach ($data as $id => $status) {
				$id = explode('_', $id);
				$id = (int) array_pop($id);
				if (empty($comments[$id])) {
					throw new \LogicException('Comment with ID `' . $id . '` does not exist!');
				}
				if ($comments[$id]->getStatus() !== $status) {
					$comments[$id]->setStatus($status);
					$changed[$id] = $comments[$id];
				}
			}

			$this->get('cms.blog.comment_edit')->saveBatch(new Blog\CommentCollection($changed), $this->get('user.current'));
		}

		return $this->redirectToReferer();
	}
}