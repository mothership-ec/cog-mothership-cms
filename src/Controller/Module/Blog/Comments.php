<?php

namespace Message\Mothership\CMS\Controller\Module\Blog;

use Message\Cog\Controller\Controller;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Content;
use Message\Mothership\CMS\Blog\FrontEndCommentException;

/**
 * Class Comments
 * @package Message\Mothership\CMS\Controller\Module\Blog
 *
 * Controller for handling blog comments on the front end of the site
 */
class Comments extends Controller
{
	const SESSION_NAME  = 'cms.blog_comment';
	const CAPTCHA_FIELD = 'captcha';

	public function display($pageID)
	{
		$comments = $this->get('cms.blog.comment_loader')->getByPage($pageID);

		return $this->render('Message:Mothership:CMS::modules:blog_comments', [
			'comments' => $comments,
			'user'     => $this->get('user.current'),
		]);
	}

	public function commentForm(Page $page, Content $content)
	{
		if ($this->get('cms.blog.comment_permission_resolver')->isVisible($content, $this->get('user.current'))) {

			$form = $this->createForm($this->get('form.blog_comment'), $this->_getDataFromSession($page->id));
			$this->get('http.session')->remove(self::SESSION_NAME . $page->id);

			return $this->render('Message:Mothership:CMS::modules:blog_comment_form', [
				'form' => $form,
				'page' => $page,
			]);
		}
		elseif (!$this->get('cms.blog.comment_permission_resolver')->userAllowed($content, $this->get('user.current'))) {
			return $this->render('Message:Mothership:CMS::modules:blog_comment_denied');
		}

		return $this->render('Message:Mothership:CMS::modules:blog_comment_disabled');
	}

	public function submitComment($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		if (!$page instanceof Page) {
			throw new \LogicException('Could not load page with ID `' . $pageID . '`');
		}

		$content = $this->get('cms.page.content_loader')->load($page);

		$form = $this->createForm($this->get('form.blog_comment'));
		$form->handleRequest();
		$data = $form->getData();

		if ($form->isValid()) {
			try {
				$comment = $this->get('cms.blog.comment_builder')->buildFromForm($pageID, $data, $content);
				$this->get('cms.blog.comment_create')->save($comment);
				$this->get('http.session')->remove(self::SESSION_NAME . $pageID);
				$this->addFlash('success', $this->trans('ms.cms.blog_comment.success'));
			} catch (FrontEndCommentException $e) {
				$this->addFlash('error', $this->trans($e->getMessage()));
			}
		}
		$this->get('http.session')->set(self::SESSION_NAME . $pageID, $data);

		return $this->redirectToReferer();
	}

	private function _getDataFromSession($pageID)
	{
		$data = $this->get('http.session')->get(self::SESSION_NAME . $pageID);

		if (!$data) {
			return null;
		}

		if (array_key_exists(self::CAPTCHA_FIELD, $data)) {
			unset($data[self::CAPTCHA_FIELD]);
		}

		return $data;
	}
}