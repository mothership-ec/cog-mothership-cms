<?php

namespace Message\Mothership\CMS\Controller\Module\Blog;

use Message\Cog\Controller\Controller;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Content;
use Message\Mothership\CMS\Blog;

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

	/**
	 * Display comments on a blog post
	 *
	 * @param $pageID
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function display($pageID)
	{
		$comments = $this->get('cms.blog.comment_loader')->getByPage($pageID, [Blog\Statuses::PENDING, Blog\Statuses::APPROVED]);

		return $this->render('Message:Mothership:CMS::modules:blog:comments', [
			'comments' => $comments,
			'user'     => $this->get('user.current'),
		]);
	}

	/**
	 * Render comment submission form
	 *
	 * @param Page $page
	 * @param Content $content
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function commentForm(Page $page, Content $content)
	{
		if ($this->get('cms.blog.comment_permission_resolver')->isVisible($content, $this->get('user.current'))) {

			$form = $this->createForm($this->get('form.blog_comment'), $this->_getDataFromSession($page->id));
			$this->get('http.session')->remove(self::SESSION_NAME . $page->id);

			return $this->render('Message:Mothership:CMS::modules:blog:comment_form', [
				'form' => $form,
				'page' => $page,
			]);
		}
		elseif (!$this->get('cms.blog.comment_permission_resolver')->userAllowed($content, $this->get('user.current'))) {
			return $this->render('Message:Mothership:CMS::modules:blog:comment_denied');
		}

		return $this->render('Message:Mothership:CMS::modules:blog:comment_disabled');
	}

	/**
	 * Process comment submission
	 *
	 * @param $pageID
	 * @throws \LogicException
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse
	 */
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
				$this->get('http.session')->remove(self::SESSION_NAME . $pageID);

				$comment = $this->get('cms.blog.comment_builder')->buildFromForm($pageID, $data, $content);
				$this->get('cms.blog.comment_create')->save($comment);
				$this->addFlash('success', $this->trans('ms.cms.blog_comment.success'));
			} catch (Blog\FrontEndCommentException $e) {
				$this->addFlash('error', $this->trans($e->getMessage()));
			}
		}
		else {
			$this->get('http.session')->set(self::SESSION_NAME . $pageID, $data);
		}

		return $this->redirectToReferer();
	}

	/**
	 * Retrieve form data
	 *
	 * @param $pageID
	 * @return array | null
	 */
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