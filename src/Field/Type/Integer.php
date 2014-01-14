<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;
use Message\Cog\Form\Handler;

/**
 * A field for an integer.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Integer extends Field
{
	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'number', $this->getLabel(), array(
			'attr' => array('data-help-key' => $this->_getHelpKeys()),
		));
	}
}