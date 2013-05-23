<?php

namespace Message\Mothership\CMS\Event;

use Message\Mothership\CMS\Page\Page;

class PageEvent extends Event
{
	protected $_page;

	public function __construct(Page $page)
	{
		$this->_page = $page;
	}

	public function getPage()
	{
		return $this->_page;
	}
}