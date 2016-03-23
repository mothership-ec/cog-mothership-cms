<?php

namespace Message\Mothership\CMS\Analytics\Provider;

use Message\Mothership\CMS\Analytics\BasicProvider;

/**
 * Class GoogleAnalyticsProvider
 * @package Message\Mothership\CMS\Analytics\Provider
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Provider representing Google Analytics older tracking code
 */
class GoogleAnalyticsProvider extends BasicProvider
{
	/**
	 * Construct with mothership default views
	 *
	 * @param $key string
	 * @param $viewReference string
	 */
	public function __construct($key, $viewReference = 'Message:Mothership:CMS::modules:analytics:google-analytics')
	{
		parent::__construct('google-analytics', $viewReference, [ 'key' => $key ]);
	}
}
