<?php

namespace Message\Mothership\CMS\Test\Field;

use Message\Mothership\CMS\Field\Field;

class FieldTest extends \PHPUnit_Framework_TestCase
{
	public function testGetName()
	{
		$name  = 'my_special_field';
		$field = $this->getMockForAbstractClass('Message\\Mothership\\CMS\\Field\\Field', array(), array($name));

		$this->assertSame($name, $field->getName());
	}
}