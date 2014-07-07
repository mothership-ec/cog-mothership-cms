<?php

namespace Message\Mothership\CMS\FieldType;

use Message\Cog\Field\Field;
use Message\Mothership\CMS\FormType;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Loader as PageLoader;

use Symfony\Component\Form\FormBuilder;

/**
 * A field for a link to an internal or external page.
 *
 * @todo Write method to convert page IDs to slugs if the scope is changed within the code base to
 * allow for easy swapping out between scopes
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Link extends Field
{
	protected $_loader;

	protected $_scope = 'any';

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
		switch ($this->_scope) {
			case self::SCOPE_CMS :
				$this->_addPageSelect($form);
				break;
			case self::SCOPE_EXTERNAL :
				// @todo use FormType\Link, or remove class if we never end up using values['scope'] thing
				$form->add($this->getName(), 'url', $this->getFieldOptions());
				break;
			default:
				$this->_addPageDatalist($form);
		}
	}

	public function setScope($scope)
	{
		if (!in_array($scope, [
			self::SCOPE_CMS,
			self::SCOPE_EXTERNAL,
			self::SCOPE_ANY,
		])) {
			throw new \InvalidArgumentException(sprintf('Invalid scope: `%s`', $scope));
		}

		$this->_scope = $scope;

		return $this;
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
		if (is_numeric($this->_value)) {
			$page = $this->_loader
				->includeDeleted(true)
				->getByID((int) $this->_value);

			if ($page instanceof Page) {
				return $page->slug->getFull();
			}
		}

		return $this->_value;
	}

	protected function _addPageSelect(FormBuilder $form)
	{
		$pages   = $this->_loader->getAll();
		$options = [];

		foreach ($pages as $page) {
			$options[$page->id] = $page->title . ' (' . $page->id . ')';
		}

		asort($options);

		$form->add($this->getName(), 'choice', [
			'multiple' => false,
			'expanded' => false,
			'choices'  => $options,
		]);
	}

	protected function _addPageDatalist(FormBuilder $form)
	{
		$pages   = $this->_loader->getAll();
		$options = [];

		foreach ($pages as $page) {
			$options[] = $page->slug->getFull();
		}

		$form->add($this->getName(), 'datalist', [
			'choices'  => $options,
		]);
	}
}