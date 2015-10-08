<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\ValueObject\Slug;
use Message\Cog\Routing\UrlMatcher;
use Message\Cog\Routing\UrlGenerator;

use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * Class SlugEdit
 * @package Message\Mothership\CMS\Page
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
class SlugEdit
{
	/**
	 * @var Loader
	 */
	private $_pageLoader;

	/**
	 * @var Edit
	 */
	private $_pageEdit;

	/**
	 * @var UrlMatcher
	 */
	private $_urlMatcher;

	/**
	 * @var UrlGenerator
	 */
	private $_urlGenerator;

	/**
	 * @param Loader $pageLoader
	 * @param Edit $pageEdit
	 * @param UrlMatcher $urlMatcher
	 * @param UrlGenerator $urlGenerator
	 */
	public function __construct(
		Loader $pageLoader,
		Edit $pageEdit,
		UrlMatcher $urlMatcher,
		UrlGenerator $urlGenerator
	)
	{
		$this->_pageLoader = $pageLoader;
		$this->_pageEdit = $pageEdit;
		$this->_urlMatcher = $urlMatcher;
		$this->_urlGenerator = $urlGenerator;
	}

	/**
	 * Check that a slug can be updated for a page and update if so
	 *
	 * @param Page $page
	 * @param Slug $slug
	 */
	public function updateSlug(Page $page, Slug $slug)
	{
		$newSlug = $slug->getLastSegment();

		if ($newSlug === $page->slug->getLastSegment()) {
			return;
		}

		$slugSegments = $page->slug->getSegments();
		array_pop($slugSegments);
		$slugSegments[] = $newSlug;
		$slug = '/'.implode('/',$slugSegments);

		$this->_checkRoute($slug);
		$this->_checkSlugExists($page, $newSlug, $slug);

		// If the slug has changed then update the slug
		if ($page->slug->getLastSegment() != $newSlug) {
			$this->_pageEdit->removeHistoricalSlug($slug);
			try {
				$this->_pageEdit->updateSlug($page, $newSlug);
			} catch (Exception\InvalidSlugException $e) {
				throw new Exception\SlugUpdateException(
					$e->getMessage(),
					'ms.cms.feedback.force-slug.failure.generic',
					[
						'%message%' => $e->getMessage(),
					]
				);
			}
		}
	}

	/**
	 * Check that no route matching the slug exists
	 *
	 * @param string $slug
	 * @throws \InvalidArgumentException        Throws exception if $slug is not a string
	 * @throws Exception\SlugUpdateException    Throws exception if route exists matching slug
	 * @throws Exception\SlugUpdateException    Rethrows exception if a ResourceNotFoundException is caught
	 */
	private function _checkRoute($slug)
	{
		if (!is_string($slug)) {
			throw new \InvalidArgumentException('Slug must be a string, ' . gettype($slug) . ' given');
		}

		try {
			$routes = $this->_urlMatcher->match($slug);

			// continue if the frontend route is the most prominent
			if ($routes['_route'] !== 'ms.cms.frontend') {
				throw new Exception\SlugUpdateException(
					'`' . $slug . '` cannot be set as it conflicts with an existing route',
					'ms.cms.feedback.force-slug.failure.reserved-route'
				);
			}
		} catch (ResourceNotFoundException $e) {
			throw new Exception\SlugUpdateException(
				$e->getMessage(),
				'ms.cms.feedback.force-slug.failure.not-matched'
			);
		}
	}

	/**
	 * Check that a no page exists with this slug
	 *
	 * @param Page $page
	 * @param $slug
	 * @throws \InvalidArgumentException        Throws exception if $slug is not a string
	 * @throws Exception\SlugUpdateException    Throws exception if page exists with slug matching that given
	 *                                          in $slug
	 */
	private function _checkSlugExists(Page $page, $slug)
	{
		if (!is_string($slug)) {
			throw new \InvalidArgumentException('Slug must be a string, ' . gettype($slug) . ' given');
		}

		$existingPage = $this->_pageLoader->getBySlug($slug, false);

		if ($existingPage && $existingPage->id != $page->id) {
			throw new Exception\SlugUpdateException(
				'Slug `' . $slug . '` has is already used',
				'ms.cms.feedback.force-slug.failure.already-used',
				[
					'%slugUrl%' => $existingPage->slug->getFull(),
					'%usingUrl%' => $this->_urlGenerator->generate('ms.cp.cms.edit.attributes', ['pageID' => $existingPage->id]),
					'%usingTitle%' => $existingPage->title,
				]
			);
		} elseif (!$existingPage) {
			$this->_checkSlugHistory($page, $slug);
		}
	}

	/**
	 * Check that a no page exists with this slug in its history
	 *
	 * @param Page $page
	 * @param $slug
	 * @throws \InvalidArgumentException        Throws exception if $slug is not a string
	 * @throws Exception\SlugUpdateException    Throws exception if page exists with slug matching that given
	 *                                          in $slug in its slug history
	 */
	private function _checkSlugHistory(Page $page, $slug)
	{
		if (!is_string($slug)) {
			throw new \InvalidArgumentException('Slug must be a string, ' . gettype($slug) . ' given');
		}

		// Check for the slug historically and show deleted ones too
		$historicalSlug = $this->_pageLoader
			->getBySlug($slug, true);

		// If there is a page returned and it's not this page then offer
		// a link to remove the slug from history and use it anyway
		if ($historicalSlug && $historicalSlug->id != $page->id) {

			$slugParts = explode('/', $slug);
			$newSlug = array_pop($slugParts);

			throw new Exception\SlugUpdateException(
				'Slug `' . $slug . '` has been previously and is redirecting to page ' . $historicalSlug->id,
				'ms.cms.feedback.force-slug.failure.redirected',
				[
					'%slug%' => $slug,
					'%redirectedUrl%' => $this->_urlGenerator->generate(
						'ms.cp.cms.edit.attributes',
						['pageID' => $historicalSlug->id]
					),
					'%redirectedTitle%' => $historicalSlug->title,
					'%forceUrl%' => $this->_urlGenerator->generate(
						'ms.cp.cms.edit.attributes.slug.force', ['pageID' => $page->id, 'slug' => $newSlug]
					),
				]
			);
		}
	}

}