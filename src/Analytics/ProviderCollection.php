<?php

namespace Message\Mothership\CMS\Analytics;

use Message\Cog\ValueObject\Collection;

/**
 * @author Samuel Trangmar-Keates
 *
 * A collection for analytics providers
 */
class ProviderCollection extends Collection
{
	/**
	 * {@inheritDoc}
	 */
	protected function _configure()
	{
		$this->setType('\\Message\\Mothership\\CMS\\Analytics\\AnalyticsProvidorInterface');
		$this->setSort(null);
		$this->setKey(function($x) {
			return $x->getName();
		});
	}
}