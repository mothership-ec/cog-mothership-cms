<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

/**
 * A field for a boolean toggle.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Boolean extends Field
{
	public function getFormField($form)
	{
		$form->add($this->getName(), 'checkbox', $this->getLabel(), array(
			'attr' => array('data-translation-key' => $this->_translationKey)
		));
	}
}