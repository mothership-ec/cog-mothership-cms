<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

/**
 * A field for text written in a rich text markup language.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class RichText extends Field
{
	protected $_engines = array(
		'markdown',
	);

	public $_engine = 'markdown';

	public function getFormField($form)
	{
		$form->add($this->getName(), 'textarea', $this->getLabel(), array(
			'attr' => array('data-translation-key' => $this->_translationKey)
		));
	}

	/**
	 * Set the rendering engine to use.
	 *
	 * @param string $engine Identifier for the rendering engine
	 */
	public function setEngine($engine)
	{
		$engine = strtolower($engine);

		if (!in_array($engine, $this->_engines)) {
			throw new \InvalidArgumentException(sprintf('Rich text engine `%s` does not exist.', $engine));
		}
	}
}