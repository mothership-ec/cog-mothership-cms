<?php

namespace Message\Mothership\CMS\Controller;

use Message\Cog\Controller\Controller;

class Frontend extends Controller
{
	public function renderPage($slug)
	{
		// Get the page
		$page = $this->get('cms.page.loader')->getBySlug($slug, false);

		// If the page was not found
		if (!$page) {
			// Check for this slug in the history, and redirect if we find a result
			if ($redirectTo = $this->get('cms.page.loader')->checkSlugHistory($slug)) {
				return $this->redirect($this->generateUrl('ms.cms.frontend', array(
					'slug' => $redirectTo->slug->getFull(),
				)), 301);
			}

			// Otherwise, throw a 404
			throw $this->createNotFoundException();
		}

		// Render the view for the page type
		return $this->render($page->type->getViewReference(), array(
			'page'    => $page,
			'content' => $this->get('cms.page.content_loader')->load($page),
		));
	}
}