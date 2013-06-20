<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

/**
 * A field for an integer.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Integer extends Field
{
	public function getFormField($form)
	{
		$form->add($this->getName(), 'number', $this->getLabel(), array(
			'attr' => array('data-translation-key' => $this->_translationKey)
		));
	}
}