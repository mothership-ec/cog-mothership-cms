<?php

namespace Message\Mothership\CMS\Blog;

/**
 * Class Statuses
 * @package Message\Mothership\CMS\Blog
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Class containing constants for comment statuses.
 */
class Statuses
{
	const PENDING  = 'pending';
	const APPROVED = 'approved';
	const SPAM     = 'spam';
	const TRASH    = 'trash';

	/**
	 * Get an array of all available comment statuses
	 *
	 * @return array
	 */
	public function getStatuses()
	{
		return [
			self::PENDING  => ucfirst(self::PENDING),
			self::APPROVED => ucfirst(self::APPROVED),
			self::SPAM     => ucfirst(self::SPAM),
			self::TRASH    => ucfirst(self::TRASH),
		];
	}
}