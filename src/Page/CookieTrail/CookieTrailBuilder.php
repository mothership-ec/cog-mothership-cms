<?php

namespace Message\Mothership\CMS\Page\CookieTrail;

use Message\Cog\ValueObject\Collection;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Loader as PageLoader;

class CookieTrailBuilder
{
	protected $_pageLoader;

	public function __construct(PageLoader $pageLoader)
	{
		$this->_pageLoader = $pageLoader;
	}

	public function getTrailByPage(Page $page)
	{
		$trail = new CookieTrail;
		$trailArray = [$page];
		$trailArray = $this->_getTrailArray($trailArray);

		foreach ($trailArray as $page) {
			$trail->add($page);
		}

		return $trail;
	}

	protected function _getTrailArray($trail)
	{
		$nextParent = $this->_pageLoader->getParent($trail[0]);

		if($nextParent == null) {
			return $trail;
		}

		array_unshift($trail, $nextParent);
		return $this->_getTrailArray($trail);
	}
}