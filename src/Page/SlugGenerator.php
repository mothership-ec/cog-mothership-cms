<?php

namespace Message\Mothership\CMS\Page;

use Message\Mothership\CMS\Exception;

use Message\Cog\ValueObject\Slug;

/**
 * Page slug generator.
 *
 * Handles generating slugs for new and existing pages and ensuring there are no
 * conflicts between the new slug and existing slugs for live pages or in the
 * history of slugs.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class SlugGenerator
{
	protected $_loader;
	protected $_substitutions;

	/**
	 * Constructor.
	 *
	 * @param Loader     $loader        The page loader instance
	 * @param array|null $substitutions Array of string substitutions to use
	 *                                  when sanitizing slugs
	 */
	public function __construct(Loader $loader, array $substitutions = null)
	{
		$this->_loader        = $loader;
		$this->_substitutions = $substitutions;
	}

	/**
	 * Generate a slug.
	 *
	 * If the generated slug already exists on a page, a flag is appended to the
	 * end to try and make the slug unique and generation is attempted again.
	 * The first time a conflict is found, -1 is appended. The second time, -2
	 * is appended, and so on until a unique slug is found.
	 *
	 * If the slug originally requested to be generated exists in the history of
	 * page slugs, a special exception is thrown to inform the user that the
	 * slug exists historically so they can optionally delete the historical
	 * slug.
	 *
	 * @param  string    $title   The page title to use for slug generation
	 * @param  Page|null $parent  The parent page, or null if it's a top level page
	 * @param  integer   $attempt Attempt number, this needn't be set except
	 *                            when called internally
	 *
	 * @return Slug               The generated slug instance
	 *
	 * @throws Exception\HistoricalSlugExistsException If the original generated
	 *                                                 slug exists in the history
	 */
	public function generate($title, Page $parent = null, $attempt = 1)
	{
		// Get the parent slug and add the title to the end
		$segments   = $parent ? $parent->slug->getSegments() : array();
		$segments[] = $title;
		$slug       = new Slug($segments);

		// Sanitize the new slug
		if ($this->_substitutions) {
			$slug->sanitize($this->_substitutions);
		}
		else {
			$slug->sanitize();
		}

		// Check to see if this slug exists in the history
		$redirectPage = $this->_loader->checkSlugHistory($slug->getFull());

		// If this slug exists in the history and this is the original
		// generation request, throw special exception
		if ($redirectPage && 1 === $attempt) {
			throw new Exception\HistoricalSlugExistsException(sprintf(
				'Slug `%s` exists historically',
				$slug->getFull()
			), $slug, $redirectPage);
		}

		// If the generated slug exists either historically or on a live page,
		// try again with a flag for uniqueness
		if ($redirectPage || $this->_loader->getBySlug($slug->getFull(), false)) {
			$newTitle  = $attempt > 1 ? substr($title, 0, strrpos($title, '-')) : $title;
			$newTitle .= '-' . $attempt;

			return $this->generate($newTitle, $parent, $attempt + 1);
		}

		// Otherwise, return the slug as it's good to use!
		return $slug;
	}
}