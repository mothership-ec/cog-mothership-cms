<?php

namespace Message\Mothership\CMS\Exception;

use Message\Mothership\CMS\Page\Page;

use Message\Cog\ValueObject\Slug;

/**
 * Exception for when a page is trying to use a slug that exists in the slug
 * history for another page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class HistoricalSlugExistsException extends Exception
{
	protected $_slug;
	protected $_page;

	/**
	 * Constructor.
	 *
	 * @param string          $message  Exception message
	 * @param Slug            $slug     The slug in question
	 * @param Page            $page     The page the slug is linked to
	 * @param integer         $code     The exception code
	 * @param \Exception|null $previous The previous exception
	 */
	public function __construct($message, Slug $slug, Page $page, $code = 0, \Exception $previous = null)
	{
		$this->_slug = $slug;
		$this->_page = $page;

		parent::__construct($message, $code, $previous);
	}

	/**
	 * Get the slug related to this exception.
	 *
	 * @return Slug
	 */
	public function getSlug()
	{
		return $this->_slug;
	}

	/**
	 * Get the page related to this exception.
	 *
	 * @return Page
	 */
	public function getPage()
	{
		return $this->_page;
	}
}