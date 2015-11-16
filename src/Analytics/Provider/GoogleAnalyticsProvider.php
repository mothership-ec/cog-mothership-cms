<?php

namespace Message\Mothership\CMS\Analytics\Provider;

use Message\Mothership\CMS\Analytics\BasicProvider;

class GoogleAnalyticsProvider extends BasicProvider
{
	/**
	 * Construct with mothership default views
	 */
	public function __construct($key, $viewReference = 'Message:Mothership:CMS::modules:analytics:google-analytics')
	{
		parent::__construct('google-analytics', $viewReference, [ 'key' => $key ]);
	}
}
