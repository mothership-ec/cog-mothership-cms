<?php

namespace Message\Mothership\CMS\Controller;

use Message\Mothership\CMS\Page;
use Message\Mothership\CMS\SearchLog\SearchLog;

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
	const SUPER_ADMIN = 'ms-super-admin';

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
	 * @param  string|null $slug The page slug
	 *
	 * @return \Message\Cog\HTTP\Response
	 *
	 * @throws NotFoundHttpException If the page could not be found or is deleted
	 * @throws NotFoundHttpException If the page is unpublished
	 * @throws AccessDeniedHttpException If the user doesn't have access to see the page
	 */
	public function renderPage($slug = null)
	{
		// Check that there is a slug, if not then show the homepage
		if (!$slug) {
			$page = $this->get('cms.page.loader')->getHomepage();
		} else {
			// Get the page
			$page = $this->get('cms.page.loader')
				->includeDeleted(false)
				->getBySlug($slug, false);
		}

		// If the page was not found
		if (!$page) {
			// Check for this slug in the history, and redirect if we find a result
			if ($redirectTo = $this->get('cms.page.loader')->checkSlugHistory($slug)) {
				return $this->redirect($this->generateUrl('ms.cms.frontend', [
					'slug' => $redirectTo->isHomepage() ? '/' : ltrim($redirectTo->slug->getFull(), '/'),
				]), 301);
			}

			// Otherwise, throw a 404
			throw $this->createNotFoundException('Page not found');
		}

		// Set the current page on the service container
		$this->_services['cms.page.current'] = function() use ($page) {
			return $page;
		};

		// Check permissions
		$auth = $this->get('cms.page.authorisation');
		$userGroups = $this->get('user.group.loader')->getByUser($this->get('user.current'));

		// Check the page is published
		if (!$auth->isPublished($page) && array_key_exists(self::SUPER_ADMIN, $userGroups)) {
			$this->addFlash('info', 'This page is not published, and can be only be viewed by super admins');
		}
		elseif (!$auth->isPublished($page)) {
			throw $this->createNotFoundException('Page exists, but it isn\'t published');
		}

		// Check if the user can access it
		if (!$auth->isViewable($page)) {
			throw $this->createAccessDeniedException();
		}

		// Set service definition for the current page content
		$content = $this->get('cms.page.content_loader')->load($page);

		$this->_services['cms.page.current.content'] = function() use ($content) {
			return $content;
		};

		// Fire event to allow listeners to add additional view parameters
		$params = $this->get('event.dispatcher')->dispatch(
			Page\Event\Event::RENDER_SET_PARAMS,
			new Page\Event\SetParametersForRenderEvent($page, $content)
		)->getParameters();

		$params = array_merge($params, array(
			'page'    => $page,
			'content' => $content,
		));

		// Fire event to allow listeners to set a Response for this request,
		// instead of rendering the page type view in the normal fashion
		$response = $this->get('event.dispatcher')->dispatch(
			Page\Event\Event::RENDER_SET_RESPONSE,
			new Page\Event\SetResponseForRenderEvent($page, $content)
		)->getResponse();

		// If a listener set the Response, return it immediately
		if ($response) {
			return $response;
		}

		// Render the view for the page type
		return $this->render($page->type->getViewReference(), $params);
	}

	/**
	 * View the search results based against a set of terms defined in the
	 * "terms" query parameter.
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function searchResults()
	{
		$termsString = $this->get('request')->get('terms');

		if (!$termsString or empty($termsString)) {
			throw $this->createNotFoundException('You must enter a term for which to search.');
		}

		// Split terms into an array on spaces & commas.
		$terms = preg_split("/[\s,]+/", $termsString);

		// Get the current page, default to first.
		$page = ($this->get('request')->get('page')) ?: 1;

		$pages = $this->get('cms.page.loader')->getBySearchTerms(
			$terms,
			1
		);

		// Log search request.
		$searchLog            = new SearchLog;
		$searchLog->term      = $termsString;
		$searchLog->referrer  = $this->get('request')->server->get('REFERER');
		$searchLog->ipAddress = $this->get('request')->getClientIp();
		$this->get('cms.search.create')->create($searchLog);

		return $this->render('::search:listing', array(
			'termsString' => $termsString,
			'pages'       => $pages,
			'pagination'  => null,
		));
	}
}