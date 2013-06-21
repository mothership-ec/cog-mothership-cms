<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;
use Message\Cog\Form\Handler;

/**
 * A field for plain text.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class Text extends Field
{
	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'text', $this->getLabel(), array(
			'attr' => array('data-help-key' => $this->_translationKey)
		));
	}
}