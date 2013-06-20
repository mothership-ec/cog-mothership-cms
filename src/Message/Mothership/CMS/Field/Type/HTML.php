<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field\Field;

/**
 * A field for some raw HTML.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class HTML extends Field
{
	public function getFormField($form)
	{
		$form->add($this->getName(), 'textarea', $this->getLabel(), array(
			'attr' => array('data-help-key' => $this->_translationKey)
		));
	}
}