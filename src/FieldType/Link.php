<?php

namespace Message\Mothership\CMS\FieldType;

use Message\Cog\Field\MultipleValueField;
use Message\Mothership\CMS\FormType;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Loader as PageLoader;

use Symfony\Component\Form\FormBuilder;

/**
 * A field for a link to an internal or external page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Link extends MultipleValueField
{
	protected $_loader;

	const SCOPE_CMS      = 'cms';
	const SCOPE_EXTERNAL = 'external';
//	const SCOPE_ROUTE    = 'route'; # for a future version?
	const SCOPE_ANY      = 'any';

	public function __construct(PageLoader $loader)
	{
		$this->_loader = $loader;
	}

	public function getFieldType()
	{
		return 'link';
	}

	public function getFormField(FormBuilder $form)
	{
		$form->add($this->getName(), new FormType\Link, $this->getFieldOptions());
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

	/**
	 * {@inheritdoc}
	 */
	public function getValueKeys()
	{
		return array(
			'scope',
			'target',
		);
	}

	/**
	 * Magic method for converting this object to a string.
	 *
	 * If the scope is "cms", the returned value is the full slug to the target
	 * page (regardless of whether this page has been deleted or not).
	 *
	 * If the scope is "external", the returned value is just the target link.
	 *
	 * @return string The evaluated link target
	 */
	public function __toString()
	{
		if (self::SCOPE_CMS === $this->_value['scope']) {
			$page = $this->_loader
				->includeDeleted(true)
				->getByID((int) $this->_value['target']);

			if ($page instanceof Page) {
				return $page->slug->getFull();
			}
		}

		return $this->_value['target'];
	}
}