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
	protected $_pages = [];

	const SCOPE_CMS      = 'cms';
	const SCOPE_EXTERNAL = 'external';
	const SCOPE_ANY      = 'any';

	const DEFAULT_LABEL = 'ms.cms.field_types.link.default_label';
	const EMPTY_VALUE   = 'ms.cms.field_types.link.empty_value';


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
				$this->_setCmsLinkOptions();
				return 'cms_link';
			case self::SCOPE_EXTERNAL :
				return 'external_link';
			default :
				$this->_setAnyLinkOptions();
				return 'any_link';
		}
	}

	public function getFormField(FormBuilder $form)
	{
		$this->_setDefaultOptions();

		switch ($this->_scope) {
			case self::SCOPE_CMS :
				$this->_addCmsLink($form);
				break;
			case self::SCOPE_EXTERNAL :
				$this->_addExternalLink($form);
				break;
			default:
				$this->_addAnyLink($form);
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

		$value = ($this->_value['scope'] === $this->_scope) ? $this->_value['target'] : $this->_convertTarget();

		return [
			'target' => $value,
			'scope'  => $this->_scope,
		];
	}

	public function getValueKeys()
	{
		return [
			'scope',
			'target',
		];
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
		return (string) $this->_convertToAny();
	}

	/**
	 * Merge field options with existing options
	 *
	 * @param array $options
	 *
	 * @return Link
	 */
	public function setFieldOptions(array $options)
	{		
		$base = $this->getFieldOptions();

		$options = array_merge($base, $options);

		parent::setFieldOptions($options);

		return $this;
	}

	/**
	 * Add a drop down of all CMS pages to the form
	 *
	 * @param FormBuilder $form
	 */
	protected function _addCmsLink(FormBuilder $form)
	{
		$form->add($this->getName(), 'cms_link', $this->getFieldOptions());
	}

	/**
	 * Add a datalist containing all existing slugs to the form.
	 *
	 * @param FormBuilder $form
	 */
	protected function _addAnyLink(FormBuilder $form)
	{
		$form->add($this->getName(), 'any_link', $this->getFieldOptions());
	}

	/**
	 * Add a URL field to the form
	 *
	 * @param FormBuilder $form
	 */
	protected function _addExternalLink(FormBuilder $form)
	{
		$form->add($this->getName(), 'external_link', $this->getFieldOptions());
	}

	/**
	 * Set the default options depending on scope.
	 * 
	 * @todo Currently setting the field options before setting the scope is kinda flakey, the label will be redefined
	 * but any other options will be overwritten as this method is called when the scope is set
	 */
	protected function _setDefaultOptions()
	{
		switch ($this->_scope) {
			case self::SCOPE_CMS :
				$this->_setCmsLinkOptions();
				break;
			case self::SCOPE_EXTERNAL :
				$this->_setExternalLinkOptions();
				break;
			default:
				$this->_setAnyLinkOptions();
		}

		$options = $this->getFieldOptions();

		if (empty($options['label'])) {
			$this->setFieldOptions([
				'label' => self::DEFAULT_LABEL,
			]);
		}
	}

	/**
	 * Load and sort all existing pages and set them as choices for the CMS link, and set the label and empty value
	 */
	protected function _setCmsLinkOptions()
	{
		if(empty($this->getFieldOptions()['choices'])) {
			$this->_setPageHeirarchy();
			$options = [];

			foreach ($this->_pages as $page) {
				$prefix             = str_repeat('-', $page->depth);
				$options[$page->id] = $prefix . ' ' . $page->title;

				if (!$page->isPublished()) {
					$options[$page->id] .= ' [unpublished]';
				}
			}

			$this->setFieldOptions([
				'choices'     => $options,
			]);
		}

		if(!empty($this->getFieldOptions()['choices'])) {
			$this->setFieldOptions([
				'empty_value' => self::EMPTY_VALUE,
			]);
		}
	}

	/**
	 * Currently does nothing, but put in place if any external link specific options need to be added
	 */
	protected function _setExternalLinkOptions()
	{
		// No options to add
	}

	/**
	 * Load and sort slugs for all existing pages and set them choices for the 'any' link, and set the label
	 */
	protected function _setAnyLinkOptions()
	{
		if(empty($this->getFieldOptions()['choices'])) {
			$this->_setPageHeirarchy();
			$options = [];

			foreach ($this->_pages as $page) {
				$options[] = $page->slug->getFull();
			}

			$this->setFieldOptions([
				'choices'  => $options,
			]);
		}
	}

	/**
	 * Load pages in a flat array so that child pages appear after their parents
	 *
	 * @param Page $parent
	 */
	protected function _setPageHeirarchy()
	{
		$this->_pages = $this->_loader->includeDeleted(false)->getAll();
	}

	/**
	 * Method to attempt to convert values from one scope to another
	 *
	 * @return int | string | null
	 */
	protected function _convertTarget()
	{
		switch ($this->_scope) {
			case self::SCOPE_CMS :
				$value = $this->_convertToCms();
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

	/**
	 * Attempt to convert value to 'cms' scope. If it can load a page using the value then it will return the id of
	 * that page, else it will try to load the page assuming the value is a slug
	 *
	 * @return int | null
	 */
	protected function _convertToCms()
	{
		if (empty($this->_value['target']) || $this->_value['scope'] === self::SCOPE_EXTERNAL) {
			return null;
		}
		elseif ($this->_value['scope'] === self::SCOPE_CMS) {
			return $this->_value['target'];
		}

		$page = $this->_loader
			->getBySlug($this->_value['target']);


		return ($page instanceof Page) ? $page->id : null;
	}

	/**
	 * Will return the current value if it is a valid URL, else it will use the convertToAny() method to convert the
	 * value to a slug
	 *
	 * @return string | null
	 */
	protected function _convertToExternalLink()
	{
		return $this->_convertToAny();
	}

	/**
	 * If the value is a valid URL or slug, it will return it, else it will assume it's an ID and try to load the
	 * page slug from that
	 *
	 * @return string | null
	 */
	protected function _convertToAny()
	{
		if (empty($this->_value['target'])) {
			return null;
		}

		if ($this->_value['scope'] === self::SCOPE_CMS) {
			$page = $this->_loader->getByID((int) $this->_value['target']);

			return ($page instanceof Page) ? $page->slug->getFull() : null;
		}

		return $this->_value['target'];
	}
}