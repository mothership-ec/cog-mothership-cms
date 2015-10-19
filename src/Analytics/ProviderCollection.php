<?php

namespace Message\Mothership\CMS\Analytics;

use Message\Cog\ValueObject\Collection;

class ProviderCollection extends Collection
{
	protected function _configure()
	{
		$this->setType('\\Message\\Mothership\\CMS\\Analytics\\AnalyticsProvidorInterface');
		$this->setSort(null);
		$this->setKey(function($x) {
			return $x->getName();
		});
	}
}