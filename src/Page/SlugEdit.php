<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\ValueObject\Slug;
use Message\Cog\Routing\UrlMatcher;
use Message\Cog\Routing\UrlGenerator;

class SlugEdit
{
	private $_pageLoader;
	private $_pageEdit;
	private $_urlMatcher;
	private $_urlGenerator;

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

	public function updateSlug(Page $page, Slug $slug, $force = false)
	{
		$newSlug = $slug->getLastSegment();

		$slugSegments = $page->slug->getSegments();
		array_pop($slugSegments);
		$slugSegments[] = $newSlug;
		$slug = '/'.implode('/',$slugSegments);
		$checkSlug = $this->_pageLoader->getBySlug($slug, false);

		try {
			$routes = $this->_urlMatcher->match($slug);

			// continue if the frontend route is the most prominent
			if ($routes['_route'] !== 'ms.cms.frontend') {
				throw new Exception\SlugUpdateException(
					'`' . $slug . '` cannot be set as it conflicts with an existing route',
					'ms.cms.feedback.force-slug.failure.reserved-route'
				);
			}
		} catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
			throw new Exception\SlugUpdateException(
				$e->getMessage(),
				'ms.cms.feedback.force-slug.failure.not-matched'
			);
		}

		// If not slug has been found, we need to check the history too
		if (!$checkSlug) {
			// Check for the slug historically and show deleted ones too
			$historicalSlug = $this->_pageLoader
				->getBySlug($slug, true);

			// If there is a page returned and it's not this page then offer
			// a link to remove the slug from history and use it anyway
			if ($historicalSlug && $historicalSlug->id != $page->id) {
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

		if ($checkSlug && $checkSlug->id != $page->id) {
			throw new Exception\SlugUpdateException(
				'Slug `' . $slug . '` has is already used',
				'ms.cms.feedback.force-slug.failure.already-used',
				[
					'%slugUrl%' => $checkSlug->slug->getFull(),
					'%usingUrl%' => $this->_urlGenerator->generate('ms.cp.cms.edit.attributes', ['pageID' => $checkSlug->id]),
					'%usingTitle%' => $checkSlug->title,
				]
			);
		}

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

}