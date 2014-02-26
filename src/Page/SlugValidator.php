<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\Exception\SlugExistsException;
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
			throw new SlugExistsException(sprintf(
				'Slug `%s` exists',
				$slug->getFull()
			), $slug, $exists);
		}

		// Check if the slug has been used historically

		// Check if the slug has been used by a deleted page
	}
}