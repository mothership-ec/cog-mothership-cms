<?php

namespace Message\Mothership\CMS\Analytics;

/**
 * @author Samuel Trangmar-Keates
 *
 * An interface for an analytics provider
 */
interface AnalyticsProvidorInterface
{
	/**
	 * Gets a unique name for the provider.
	 * This is used as an internal identifier.
	 * 
	 * @return string Unique name
	 */
	public function getName();

	/**
	 * Gets the view reference to render on each page
	 * 
	 * @return string The view reference
	 */
	public function getViewReference();

	/**
	 * Gets the view parameters
	 * 
	 * @return array Parameters for the view
	 */
	public function getViewParams();
}