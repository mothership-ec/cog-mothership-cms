<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\ValueObject\Collection;

class PageCollection extends Collection
{
	protected function _configure()
	{
		$this->setType('\\Message\\Mothership\\CMS\\Page\\Page');
		$this->setKey('id');
	}
}