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
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Link extends MultipleValueField
{
	protected $_loader;

	protected $_scope = 'any';

	const SCOPE_CMS      = 'cms';
	const SCOPE_EXTERNAL = 'external';
//	const SCOPE_ROUTE    = 'route'; # for a future version?
	const SCOPE_ANY      = 'any';

	public function __construct(PageLoader $loader)
	{
		$loader->includeDeleted(true);

		$this->_loader = $loader;
	}

	public function getFieldType()
	{
		return 'link';
	}

	public function getFormType()
	{
		switch ($this->_scope) {
			case self::SCOPE_CMS :
				$this->_setSelectOptions();
				return 'cms_link';
			case self::SCOPE_EXTERNAL :
				return 'external_link';
			default :
				$this->_setDatalistOptions();
				return 'any_link';
		}
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
		$scope = strtolower($scope);

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
	 * Method to ensure that if the scope changes from CMS to something else, it returns the right value
	 *
	 * @return int|null|string
	 */
	public function getValue()
	{
		if (empty($this->_value)) {
			return null;
		}

		if ($this->_value['scope'] !== $this->_scope) {
			$this->_convertTarget();
		}

		return $this->_value['target'];
	}

	public function getValueKeys()
	{
		return [
			'scope',
			'target',
		];
	}

	protected function _convertTarget()
	{
		switch ($this->_scope) {
			case self::SCOPE_CMS :
				$value = $this->_convertToCMS();
				break;
			case self::SCOPE_EXTERNAL :
				$value = $this->_convertToExternalLink();
				break;
			default :
				$value = $this->_convertToAny();
				break;
		}

		return $value;
	}

	protected function _convertToCMS()
	{
		if ($page = $this->_loader->getByID($this->_value['target'])) {
			return $page->id;
		}

		$page = $this->_loader
			->getBySlug($this->_value['target']);

		return ($page instanceof Page) ? $page->id : null;
	}

	protected function _convertToExternalLink()
	{
		if (filter_var($this->_value['target'], FILTER_VALIDATE_URL)) {
			return $this->_value['target'];
		}

		return $this->_convertToAny();
	}

	protected function _convertToAny()
	{
		if (filter_var($this->_value['target'], FILTER_VALIDATE_URL) || substr($this->_value['target'], 0) === '/') {
			return $this->_value['target'];
		}

		$page = $this->_loader
			->getByID((int) $this->_value);

		return ($page instanceof Page) ? $page->slug->getFull() : null;

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
		return $this->_convertToAny();
//
//		if ($this->_value['scope'] === self::SCOPE_CMS) {
//			return ($this->_getSlugFromID()) ?: $this->_value['target'];
//		}
//
//		return $this->_value['target'];
	}

	protected function _addPageSelect(FormBuilder $form)
	{
		$this->_setSelectOptions();

		$form->add($this->getName(), 'choice', $this->getFieldOptions());
	}

	protected function _addPageDatalist(FormBuilder $form)
	{
		$this->_setDatalistOptions();

		$form->add($this->getName(), 'datalist', $this->getFieldOptions());
	}

	protected function _setSelectOptions()
	{
		$pages   = $this->_loader->getAll();
		$options = [];

		foreach ($pages as $page) {
			$options[$page->id] = $page->title;
		}

		asort($options);

		$this->setFieldOptions([
			'multiple' => false,
			'expanded' => false,
			'choices'  => $options,
		]);
	}

	protected function _setDatalistOptions()
	{
		$pages   = $this->_loader->getAll();
		$options = [];

		foreach ($pages as $page) {
			$options[] = $page->slug->getFull();
		}

		$this->setFieldOptions([
			'choices'  => $options,
		]);
	}
}