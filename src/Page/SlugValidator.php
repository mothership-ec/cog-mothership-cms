<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\Exception;
use Message\Cog\ValueObject\Slug;

class SlugValidator
{
	protected $_loader;

	public function __construct(Loader $loader)
	{
		$this->_loader = $loader;
	}

	public function validate(Slug $slug)
	{
		// Check if the slug is currently in use
		if ($exists = $this->_loader->getBySlug($slug, false)) {
			throw new Exception\SlugExistsException(sprintf(
				'Slug `%s` exists',
				$slug->getFull()
			), $slug, $exists);
		}

		// Check if the slug has been used historically
		if ($historical = $this->_loader->checkSlugHistory($slug->getFull())) {
			throw new Exception\HistoricalSlugExistsException(sprintf(
				'Slug `%s` exists historically',
				$slug->getFull()
			), $slug, $historical);
		}

		// Check if the slug has been used by a deleted page
		$this->_loader->includeDeleted(true);
		if ($deleted = $this->_loader->getBySlug($slug, false)) {
			throw new Exception\DeletedSlugExistsException(sprintf(
				'Slug `%s` exists on a deleted page',
				$slug->getFull()
			), $slug, $deleted);
		}
		$this->_loader->includeDeleted(false);

		return $slug;
	}
}