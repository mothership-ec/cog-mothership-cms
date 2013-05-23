<?php

namespace Message\Mothership\CMS\Test\Page\Field;

use Message\Mothership\CMS\Page\Field\MultipleValueField;

class MultipleValueFieldTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$fields = array(
			'productID'  => 53,
			'colourID'   => 62,
			'someOption' => true,
			'label'      => 'dunno',
		);

		$group = new MultipleValueField($fields);

		$this->assertSame($fields['productID'], $group->productID);
		$this->assertSame($fields['colourID'], $group->colourID);
		$this->assertSame($fields['someOption'], $group->someOption);
		$this->assertSame($fields['label'], $group->label);

		return $group;
	}

	/**
	 * @depends testConstructor
	 */
	public function testOutputting($group)
	{
		echo $group;

		$this->expectOutputString('53:62:1:dunno');
	}

	public function testGettingSetting()
	{
		$group = new MultipleValueField;

		$group->productID = 55;
		$group->add('colour', 'red');

		$this->assertSame(55, $group->productID);
		$this->assertSame('red', $group->colour);

		return $group;
	}

	/**
	 * @depends testGettingSetting
	 */
	public function testIsset(MultipleValueField $field)
	{
		$this->assertTrue(isset($field->productID));
		$this->assertFalse(isset($field->thisIsNotSet));
	}

	/**
	 * @expectedException        \OutOfBoundsException
	 * @expectedExceptionMessage does not exist
	 */
	public function testGettingUndefinedField()
	{
		$field = new MultipleValueField;

		$field->iDunnoSomeField;
	}

	/**
	 * @dataProvider Message\Mothership\CMS\Test\Page\Field\GroupTest::getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyName($value)
	{
		$field = new MultipleValueField;
		$field->add($value, 'My value');
	}

	/**
	 * @dataProvider Message\Mothership\CMS\Test\Page\Field\GroupTest::getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyNameAsProperty($value)
	{
		$field = new MultipleValueField;
		$field->{$value} = 90;
	}

	/**
	 * @dataProvider Message\Mothership\CMS\Test\Page\Field\GroupTest::getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyNameConstructor($value)
	{
		$field = new MultipleValueField(array(
			$value => 'Some value',
		));
	}
}