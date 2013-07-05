<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;
use Message\Cog\Form\Handler;

/**
 * A field for a single date & time.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class DateTime extends Field
{
	public function getFormField(Handler $form)
	{
		$form->add($this->getName(), 'datetime', $this->getLabel(), array(
			'attr' => array('data-help-key' => $this->_getHelpKeys()),
		));
	}
}