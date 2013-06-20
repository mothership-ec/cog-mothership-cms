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

	public function testConstructor()
	{
		$fields = array(
			'title' => $this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('My Title')),
			'body' => $this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('My body text for this field')),
			'url' => $this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('http://www.message.co.uk')),
		);

		$group = new Group($fields);

		$this->assertSame($fields['title'], $group->title);
		$this->assertSame($fields['body'], $group->body);
		$this->assertSame($fields['url'], $group->url);
	}

	public function testGettingSetting()
	{
		$group = new Group;
		$field = $this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('This is a special field'));

		$group->myField = $field;
		$group->add('anotherField', $field);

		$this->assertSame($field, $group->myField);
		$this->assertSame($field, $group->anotherField);

		return $group;
	}

	/**
	 * @depends testGettingSetting
	 */
	public function testIsset(Group $group)
	{
		$this->assertTrue(isset($group->myField));
		$this->assertFalse(isset($group->thisIsNotSet));
	}

	/**
	 * @expectedException        \OutOfBoundsException
	 * @expectedExceptionMessage does not exist
	 */
	public function testGettingUndefinedField()
	{
		$group = new Group;

		$group->iDunnoSomeField;
	}

	/**
	 * @dataProvider getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyName($value)
	{
		$group = new Group;
		$group->add($value, $this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('This is a special field')));
	}

	/**
	 * @dataProvider getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyNameAsProperty($value)
	{
		$group = new Group;
		$group->{$value} = $this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('This is a special field'));
	}

	/**
	 * @dataProvider getFalseyValues
	 *
	 * @expectedException        \InvalidArgumentException
	 * @expectedExceptionMessage must have a name
	 */
	public function testAddingFalseyNameConstructor($value)
	{
		$group = new Group(array(
			$value => $this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('This is a special field')),
		));
	}
}