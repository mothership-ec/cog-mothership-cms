<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

use Message\Mothership\FileManager\File\Type as FileType;

use Message\Cog\Form\Handler;
use Message\Cog\Filesystem;
use Message\Cog\Service\ContainerInterface;
use Message\Cog\Service\ContainerAwareInterface;

/**
 * A field for a product from the products database.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Product extends Field implements ContainerAwareInterface
{
	protected $_services;

	/**
	 * {@inheritdoc}
	 */
	public function setContainer(ContainerInterface $container)
	{
		$this->_services = $container;
	}

	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'choice', $this->getLabel(), array(
			'attr'          => array('data-help-key' => $this->_getHelpKeys()),
			'choices'       => $this->_getChoices(),
			'empty_value'   => 'Please select a product...',
		));
	}

	public function getProduct()
	{
		return $this->_services['product.loader']->getByID((int) $this->_value);
	}

	// public function getValue()
	// {
	// 	return $this->getProduct();
	// }

	protected function _getChoices()
	{
		$choices = array();

		foreach ($this->_services['product.loader']->getAll() as $product) {
			$choices[$product->id] = $product->displayName ?: $product->name;
		}

		return $choices;
	}
}