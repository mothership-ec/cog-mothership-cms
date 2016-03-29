<?php

namespace Message\Mothership\CMS\Analytics;

/**
 * @author Samuel Trangmar-Keates
 *
 * A basic provider class which is constructed with the reference, params and names
 * required. Useful for basic providers.
 *
 * If there is a more complecated provider, then AnalyticsProviderInterface should
 * be implemented
 */
class BasicProvider implements AnalyticsEditableProviderInterface
{
	/**
	 * @var string
	 */
	private $_name;

	/**
	 * @var string
	 */
	private $_viewRef;

	/**
	 * @var array
	 */
	private $_params;

	/**
	 * Construct the object with the correct fields.
	 * 
	 * @param string $name    The object identifier
	 * @param string $viewRef The view reference
	 * @param array  $params  The parameters to pass to the view
	 */
	public function __construct($name, $viewRef, array $params = [])
	{
		$this->_name = $name;
		$this->setViewReference($viewRef);
		$this->setViewParams($params);
	}

	/**
	 * {@inheritDoc}
	 */
	public function getViewReference()
	{
		return $this->_viewRef;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setViewReference($viewRef)
	{
		if (!is_string($viewRef)) {
			throw new \InvalidArgumentException('View reference must be a string, ' . gettype($viewRef) . ' given');
		}

		$this->_viewRef = $viewRef;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getViewParams()
	{
		return $this->_params;
	}

	/**
	 * {@inheritDoc}
	 */
	public function setViewParams(array $params)
	{
		$this->_params = $params;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return $this->_name;
	}
}