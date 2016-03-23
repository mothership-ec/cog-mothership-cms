<?php

namespace Message\Mothership\CMS\Analytics\Provider;

use Message\Mothership\CMS\Analytics\BasicProvider;

/**
 * Class GoogleAnalyticsUniversalProvider
 * @package Message\Mothership\CMS\Analytics\Provider
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Provider representing Google Analytics newer 'universal' tracking code (see https://support.google.com/analytics/answer/2790010?hl=en)
 */
class GoogleAnalyticsUniversalProvider extends BasicProvider
{
	/**
	 * Construct with mothership default views
	 *
	 * @param $key string
	 * @param $viewReference string
	 */
	public function __construct($key, $viewReference = 'Message:Mothership:CMS::modules:analytics:google-analytics-universal')
	{
		parent::__construct('google-analytics-universal', $viewReference, [ 'key' => $key ]);
	}
}
