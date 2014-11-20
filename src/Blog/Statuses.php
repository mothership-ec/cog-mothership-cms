<?php

namespace Message\Mothership\CMS\Blog;

class Statuses
{
	const PENDING  = 'pending';
	const APPROVED = 'approved';
	const SPAM     = 'spam';
	const TRASH    = 'trash';

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