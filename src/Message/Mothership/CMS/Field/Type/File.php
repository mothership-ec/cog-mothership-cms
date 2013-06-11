<?php

namespace Message\Mothership\CMS\Field\Type;

use Message\Mothership\CMS\Field;

use Message\Mothership\FileManager\File\Type as FileType;

class File extends Field\MultipleValueField
{
	protected $_allowedTypes;

	public function getFormField()
	{
		// i dunno :(
	}

	public function setAllowedTypes($types)
	{
		// set the allowed file types (image = 1 etc, check big paper)
	}
}