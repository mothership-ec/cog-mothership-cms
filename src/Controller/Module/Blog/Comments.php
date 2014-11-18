<?php

namespace Message\Mothership\CMS\Controller\Module\Blog;

use Message\Cog\Controller\Controller;

use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Content;
use Message\Mothership\CMS\Blog\FrontEndCommentException;
use Message\Mothership\CMS\Blog\ContentOptions;
use Message\Mothership\CMS\Blog\InvalidContentException;

class Comments extends Controller
{
	const SESSION_NAME  = 'cms.blog_comment';
	const CAPTCHA_FIELD = 'captcha';

	public function commentForm(Page $page, Content $content)
	{
		$validationError = false;

		try {
			$this->get('cms.blog.content_validator')->validate($content);
		}
		catch (InvalidContentException $e) {
			$validationError = true;
		}

		if ($validationError === false &&
			($content->{ContentOptions::COMMENTS}->{ContentOptions::ALLOW_COMMENTS}->getValue() !== ContentOptions::DISABLED)) {

			$form = $this->createForm($this->get('form.blog_comment'), $this->_getDataFromSession($page->id));

			return $this->render('Message:Mothership:CMS::modules:blog_comment', [
				'form' => $form,
				'page' => $page,
			]);
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