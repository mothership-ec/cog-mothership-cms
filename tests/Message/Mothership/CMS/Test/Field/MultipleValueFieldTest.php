<?php

namespace Message\Mothership\CMS\Test\Field;

use Message\Mothership\CMS\Field\MultipleValueField;

class MultipleValueFieldTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructor()
	{
		$this->markTestIncomplete('revisit this');
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
		$this->markTestIncomplete('revisit this');
		echo $group;

		$this->expectOutputString('53:62:1:dunno');
	}

	public function testGettingSetting()
	{
		$this->markTestIncomplete('revisit this');
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
		$this->markTestIncomplete('revisit this');
		$this->assertTrue(isset($field->productID));
		$this->assertFalse(isset($field->thisIsNotSet));
	}

	/**
	 * @expectedException        \OutOfBoundsException
	 * @expectedExceptionMessage does not exist
	 */
	public function testGettingUndefinedField()
	{
		$this->markTestIncomplete('revisit this');
		$field = new MultipleValueField;

		$field->iDunnoSomeField;
	}

	/**
	 * @dataProvider Message\Mothership\CMS\Test\Field\GroupTest::getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyName($value)
	{
		$this->markTestIncomplete('revisit this');
		$field = new MultipleValueField;
		$field->add($value, 'My value');
	}

	/**
	 * @dataProvider Message\Mothership\CMS\Test\Field\GroupTest::getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyNameAsProperty($value)
	{
		$this->markTestIncomplete('revisit this');
		$field = new MultipleValueField;
		$field->{$value} = 90;
	}

	/**
	 * @dataProvider Message\Mothership\CMS\Test\Field\GroupTest::getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyNameConstructor($value)
	{
		$this->markTestIncomplete('revisit this');
		$field = new MultipleValueField(array(
			$value => 'Some value',
		));
	}
}