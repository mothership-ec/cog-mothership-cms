<?php

namespace Message\Mothership\CMS\Test\Field;

use Message\Mothership\CMS\Field\RepeatableContainer;

class RepeatableContainerTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructorAndIteration()
	{
		$groups = array(
			0 => $this->getMock('Message\Mothership\CMS\Field\Group', array(), array('mygroup')),
			1 => $this->getMock('Message\Mothership\CMS\Field\Group', array(), array('anothergroup')),
			2 => $this->getMock('Message\Mothership\CMS\Field\Group', array(), array('third')),
		);

		$groups[0]->add($this->getMockForAbstractClass('Message\Mothership\CMS\Field\Field', array('my_field')));
		$groups[0]->add($this->getMockForAbstractClass('Message\Mothership\CMS\Field\MultipleValueField', array('multi_field')));

		$groups[1]->add($this->getMockForAbstractClass('Message\Mothership\CMS\Field\Field', array('my_field')));
		$groups[1]->add($this->getMockForAbstractClass('Message\Mothership\CMS\Field\MultipleValueField', array('multi_field')));

		$groups[2]->add($this->getMockForAbstractClass('Message\Mothership\CMS\Field\Field', array('my_field')));
		$groups[2]->add($this->getMockForAbstractClass('Message\Mothership\CMS\Field\MultipleValueField', array('multi_field')));

		$fields = new RepeatableContainer($groups);

		foreach ($fields as $key => $fieldGroup) {
			$this->assertSame($groups[$key], $fieldGroup);
		}

		return $fields;
	}

	/**
	 * @depends testConstructorAndIteration
	 */
	public function testCount($fields)
	{
		$this->assertSame(3, $fields->count());
		$this->assertSame(3, count($fields));
	}

	/**
	 * @depends testConstructorAndIteration
	 */
	public function testAddingAndGetting($fields)
	{
		$this->assertSame(3, count($fields));

		$group = $this->getMock('Message\Mothership\CMS\Field\Group', array(), array('groupdude'));
		$fields->add($group);

		$this->assertSame(4, count($fields));

		foreach ($fields as $key => $fieldGroup) {
			if (3 === $key) {
				$this->assertSame($group, $fieldGroup);
			}
		}

		$this->assertSame($group, $fields->get(3));
	}

	public function testGetIterator()
	{
		$fields = new RepeatableContainer;

		$this->assertInstanceOf('\Traversable', $fields->getIterator());
	}
}