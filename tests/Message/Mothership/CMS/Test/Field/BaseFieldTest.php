<?php

namespace Message\Mothership\CMS\Test\Field;

use Message\Mothership\CMS\Field\BaseField;

class BaseFieldTest extends \PHPUnit_Framework_TestCase
{
	protected $_field;

	public function setUp()
	{
		$this->_field = $this->getMockForAbstractClass('Message\\Mothership\\CMS\\Field\\BaseField', array(
			'my_field',
			'This is my field'
		));
	}

	public function testLocalisableToggle()
	{
		$this->assertFalse($this->_field->isLocalisable());
		$this->assertSame($this->_field, $this->_field->setLocalisable(true));
		$this->assertTrue($this->_field->isLocalisable());
		$this->assertSame($this->_field, $this->_field->setLocalisable(false));
		$this->assertFalse($this->_field->isLocalisable());
	}

	public function testGetNameAndLabel()
	{
		$this->assertEquals('my_field', $this->_field->getName());
		$this->assertEquals('This is my field', $this->_field->getLabel());
	}

	public function testToString()
	{
		$this->_field
			->expects($this->any())
			->method('getValue')
			->will($this->returnValue('value'));

		echo $this->_field;

		$this->expectOutputString('value');
	}
}