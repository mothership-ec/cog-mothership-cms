<?php

namespace Message\Mothership\CMS\Analytics;

/**
 * Interface AnalyticsEditableProviderInterface
 * @package Message\Mothership\CMS\Analytics
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Extension to analytics provider interface allowing view references and parameters to be overridden
 */
interface AnalyticsEditableProviderInterface extends AnalyticsProviderInterface
{
	/**
	 * Set the view reference for the provider
	 *
	 * @param string $viewRef
	 */
	public function setViewReference($viewRef);

	/**
	 * Set the view parameters for the provider
	 *
	 * @param array $params
	 */
	public function setViewParams(array $params);
}