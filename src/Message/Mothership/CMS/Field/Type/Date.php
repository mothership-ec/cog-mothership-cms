<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

/**
 * A field for a single date.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Date extends Field
{
	public function getFormField($form)
	{
		$form->add($this->getName(), 'datetime', $this->getLabel(), array(
			'attr' => array('data-translation-key' => $this->_translationKey)
		));
	}
}