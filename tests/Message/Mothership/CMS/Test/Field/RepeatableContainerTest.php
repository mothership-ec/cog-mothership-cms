<?php

namespace Message\Mothership\CMS\Test\Field;

use Message\Mothership\CMS\Field\RepeatableContainer;

class RepeatableContainerTest extends \PHPUnit_Framework_TestCase
{
	public function testConstructorAndIteration()
	{
		$groups = array(
			$this->getMock('Message\Mothership\CMS\Page\Field\Group', array(), array(array(
				$this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('Field value')),
				$this->getMock('Message\Mothership\CMS\Page\Field\MultipleValueField', array(), array(array(55, 24))),
			))),
			$this->getMock('Message\Mothership\CMS\Page\Field\Group'),
			$this->getMock('Message\Mothership\CMS\Page\Field\Group', array(), array(array(
				$this->getMock('Message\Mothership\CMS\Page\Field\Field', array(), array('My title'))
			))),
		);

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
	public function testAdding($fields)
	{
		$this->assertSame(3, count($fields));

		$group = $this->getMock('Message\Mothership\CMS\Page\Field\Group');
		$fields->add($group);

		$this->assertSame(4, count($fields));

		foreach ($fields as $key => $fieldGroup) {
			if (3 === $key) {
				$this->assertSame($group, $fieldGroup);
			}
		}
	}

	public function testGetIterator()
	{
		$fields = new RepeatableContainer;

		$this->assertInstanceOf('\Traversable', $fields->getIterator());
	}
}