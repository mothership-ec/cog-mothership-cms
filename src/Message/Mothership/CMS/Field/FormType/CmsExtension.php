<?php

namespace Message\Mothership\CMS\Field\FormType;

use Symfony\Component\Form\AbstractExtension;

class CmsExtension extends AbstractExtension
{
	protected function loadTypes()
	{
		return array(
			new Link,
			new MothershipFile,
		);
	}
}