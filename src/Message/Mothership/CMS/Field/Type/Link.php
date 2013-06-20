<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field;

/**
 * A field for a link to an internal page
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Link extends Field\MultipleValueField
{
	const SCOPE_CMS      = 'cms';
	const SCOPE_EXTERNAL = 'external';
//	const SCOPE_ROUTE    = 'route'; # for a future version?
	const SCOPE_ANY      = 'any';

	public function getFormField()
	{
		// i dunno :(
	}

	public function setScope($scope)
	{
		if (!in_array($scope, array(
			self::SCOPE_CMS,
			self::SCOPE_EXTERNAL,
			self::SCOPE_ANY,
		))) {
			throw new \InvalidArgumentException(sprintf('Invalid scope: `%s`', $scope));
		}

		// actually, maybe this makes more sense on the form field object?

		return $this;
	}

	public function getValueKeys()
	{
		return array(
			'scope',
			'target',
		);
	}
}