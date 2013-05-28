<?php

namespace Message\Mothership\CMS\Page\Field\Type;

use Message\Mothership\CMS\Page\Field;

class File extends MultipleValueField
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