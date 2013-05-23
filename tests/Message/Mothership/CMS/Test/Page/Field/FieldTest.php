<?php

namespace Message\Mothership\CMS\Test\Page\Field;

use Message\Mothership\CMS\Page\Field\Field;

class FieldTest extends \PHPUnit_Framework_TestCase
{
	public function testOutputting()
	{
		$value = 'Hello, I am testing this functionality.';
		$field = new Field($value);

		echo $field;

		$this->expectOutputString($value);
	}
}