<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field;

class File extends Field\MultipleValueField
{
	public function getFormField()
	{
		// i dunno :(
	}

	public function setAllowedTypes($types)
	{
		// set the allowed file types (image = 1 etc, check big paper)
	}
}