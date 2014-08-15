<?php

namespace Message\Mothership\CMS\Page\CookieTrail;

use Message\Cog\ValueObject\Collection;
use Message\Mothership\CMS\Page\Page;

class CookieTrailBuilder
{
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
		$nextParent = $trail[0]->getParent();

		if($nextParent == null) {
			return $trail;
		}

		array_unshift($nextParent, $trail);
		return $this->_getTrailArray();
	}
}