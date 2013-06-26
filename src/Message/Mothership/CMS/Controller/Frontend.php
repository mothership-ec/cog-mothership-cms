<?php

namespace Message\Mothership\CMS\Controller;

use Message\Cog\Controller\Controller;

/**
 * Frontend controller for the CMS.
 *
 * Handles the rendering of CMS pages.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Frontend extends Controller
{
	/**
	 * Render a CMS page by a full slug.
	 *
	 * If the slug was found in the history, the user is redirected to the target
	 * page with a 301 status code.
	 *
	 * The page can't be accessed if:
	 *
	 *  - The page is deleted
	 *  - The page is unpublished
	 *  - The user doesn't have sufficient access to see the page
	 *
	 * If the page is good to be rendered, it is added to the service container
	 * under the `cms.page.current` identifier.
	 *
	 * @param  string $slug The page slug
	 *
	 * @return Response
	 *
	 * @throws NotFoundHttpException If the page could not be found or is deleted
	 * @throws NotFoundHttpException If the page is unpublished
	 * @throws AccessDeniedHttpException If the user doesn't have access to see the page
	 */
	public function renderPage($slug)
	{
		// Get the page
		$page = $this->get('cms.page.loader')
			->includeDeleted(false)
			->getBySlug($slug, false);

		// If the page was not found
		if (!$page) {
			// Check for this slug in the history, and redirect if we find a result
			if ($redirectTo = $this->get('cms.page.loader')->checkSlugHistory($slug)) {
				return $this->redirect($this->generateUrl('ms.cms.frontend', array(
					'slug' => ltrim($redirectTo->slug->getFull(), '/'),
				)), 301);
			}

			// Otherwise, throw a 404
			throw $this->createNotFoundException('Page not found');
		}

		// Check permissions
		$auth = $this->get('cms.page.authorisation');

		// Check the page is published
		if (!$auth->isPublished($page)) {
			throw $this->createNotFoundException('Page exists, but it isn\'t published');
		}

		// Check if the user can access it
		if (!$auth->isViewable($page)) {
			throw $this->createAccessDeniedException();
		}

		// If all's well, set the current page on the service container
		$this->_services['cms.page.current'] = function() use ($page) {
			return $page;
		};

		// Render the view for the page type
		return $this->render($page->type->getViewReference(), array(
			'page'    => $page,
			'content' => $this->get('cms.page.content_loader')->load($page),
		));
	}
}