<?php

namespace Message\Mothership\CMS\Test\Field;

use Message\Mothership\CMS\Field\Group;

class GroupTest extends \PHPUnit_Framework_TestCase
{
	static public function getFalseyValues()
	{
		return array(
			array(''),
			array(false),
			array(0),
			array(null),
			array(0.000),
		);
	}

	public function testGetNameAndLabel()
	{
		$group = new Group('my_group', 'My lovely group');

		$this->assertSame('my_group', $group->getName());
		$this->assertSame('My lovely group', $group->getLabel());
	}

	public function testGettingSetting()
	{
		$group = new Group('group1');
		$field = $this->getMockForAbstractClass('Message\Mothership\CMS\Field\Field', array('my_field'));

		$group->add($field);

		$this->assertSame($field, $group->my_field);

		return $group;
	}

	/**
	 * @depends testGettingSetting
	 */
	public function testIsset(Group $group)
	{
		$this->assertTrue(isset($group->my_field));
		$this->assertFalse(isset($group->thisIsNotSet));
	}

	/**
	 * @expectedException        \OutOfBoundsException
	 * @expectedExceptionMessage does not exist
	 */
	public function testGettingUndefinedField()
	{
		$group = new Group('group2');

		$group->iDunnoSomeField;
	}

	public function testRepeatable()
	{
		$group = new Group('group');

		$this->assertFalse($group->isRepeatable());
		$this->assertSame($group, $group->setRepeatable(true));
		$this->assertTrue($group->isRepeatable());
		$this->assertSame($group, $group->setRepeatable(false));
		$this->assertFalse($group->isRepeatable());
	}

	public function testIdentifierField()
	{
		$group = new Group('my_group');
		$field = $this->getMockForAbstractClass('Message\Mothership\CMS\Field\Field', array('description'));

		$this->assertFalse($group->getIdentifierField());

		// Test automatic setting of field doesn't happen for a non-titley field
		$group->add($field);

		$this->assertFalse($group->getIdentifierField());

		$this->assertSame($group, $group->setIdentifierField('description'));
		$this->assertSame($field, $group->getIdentifierField());
	}

	/**
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage does not exist
	 */
	public function testSetUnknownIdentifierField()
	{
		$group = new Group('my_group');

		$group->setIdentifierField('dunno_what_this_is');
	}

	/**
	 * @dataProvider getIdentifierFields
	 */
	public function testAutomaticIdentifierFieldSetting()
	{

	}

	public function testIdentifierFieldNotSetAutomaticallyWhenAlreadySet()
	{
		$group = new Group('group5');
		$field = $this->getMockForAbstractClass('Message\Mothership\CMS\Field\Field', array('my_field'));

		$group
			->add($field)
			->setIdentifierField('my_field');

		$group->add($this->getMockForAbstractClass('Message\Mothership\CMS\Field\Field', array('title')));

		$this->assertSame($field, $group->getIdentifierField);
	}
}