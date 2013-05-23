<?php

namespace Message\Mothership\CMS\Test\Page;

use Message\Mothership\CMS\Page\ContentLoader;

use Message\Cog\DB\Adapter\Faux\Connection;
use Message\Cog\DB\Query;

class ContentLoaderTest extends \PHPUnit_Framework_TestCase
{
	protected $_loader;

	public function setUp()
	{
		$connection = new Connection;
		// For testDuplicateFieldNameException
		$connection->setPattern('/page_id([\s]+?)= 1/', array(
			array(
				'field'     => 'myField',
				'value'     => 'My Value',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'myField',
				'value'     => 'Some name',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
		));
		// For testFieldGroupNameCollisionException
		$connection->setPattern('/page_id([\s]+?)= 2/', array(
			array(
				'field'     => 'title',
				'value'     => 'My special title',
				'group'     => 'special',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'body',
				'value'     => 'Some name',
				'group'     => 'special',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'special',
				'value'     => 'Special field, not in a group',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
		));
		// For testMultipleValueFieldOnNormalFieldException
		$connection->setPattern('/page_id([\s]+?)= 3/', array(
			array(
				'field'     => 'fieldName',
				'value'     => 'My special title',
				'group'     => 'group',
				'sequence'  => 0,
				'data_name' => 'part1',
			),
			array(
				'field'     => 'fieldName',
				'value'     => 'Some name',
				'group'     => 'group',
				'sequence'  => 0,
				'data_name' => 'part2',
			),
			array(
				'field'     => 'fieldName',
				'value'     => 'Special field, not in a group',
				'group'     => 'group',
				'sequence'  => 0,
				'data_name' => null,
			),
		));
		// For testSequenceNumberOnUngroupedFieldException
		$connection->setPattern('/page_id([\s]+?)= 4/', array(
			array(
				'field'     => 'normalField',
				'value'     => 'Some value',
				'group'     => null,
				'sequence'  => 1,
				'data_name' => null,
			),
		));
		// For testLoading
		$connection->setPattern('/page_id([\s]+?)= 5/', array(
			array(
				'field'     => 'normalField',
				'value'     => 'Some value',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'multiValueField',
				'value'     => 'This is part 1!',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => 'part_1',
			),
			array(
				'field'     => 'multiValueField',
				'value'     => 'This is part 2!',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => 'part_2',
			),
			array(
				'field'     => 'text',
				'value'     => 'BUY BUY BUY BUY NOW!',
				'group'     => 'callToAction',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'product',
				'value'     => 8795,
				'group'     => 'callToAction',
				'sequence'  => 0,
				'data_name' => 'productID',
			),
			array(
				'field'     => 'product',
				'value'     => 120,
				'group'     => 'callToAction',
				'sequence'  => 0,
				'data_name' => 'colourID',
			),
			array(
				'field'     => 'link',
				'value'     => '/my-page',
				'group'     => 'callToAction',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'title',
				'value'     => 'Check out our hats!',
				'group'     => 'promo',
				'sequence'  => 1,
				'data_name' => null,
			),
			array(
				'field'     => 'description',
				'value'     => 'You can wear them. On your head.',
				'group'     => 'promo',
				'sequence'  => 1,
				'data_name' => null,
			),
			array(
				'field'     => 'image',
				'value'     => 1089,
				'group'     => 'promo',
				'sequence'  => 1,
				'data_name' => 'assetID',
			),
			array(
				'field'     => 'image',
				'value'     => 200,
				'group'     => 'promo',
				'sequence'  => 1,
				'data_name' => 'width',
			),
			array(
				'field'     => 'image',
				'value'     => 250,
				'group'     => 'promo',
				'sequence'  => 1,
				'data_name' => 'height',
			),
			array(
				'field'     => 'title',
				'value'     => 'Check out our socks!',
				'group'     => 'promo',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'description',
				'value'     => 'They are really warm and cosy. And cheap. And great. Buy them!',
				'group'     => 'promo',
				'sequence'  => 0,
				'data_name' => null,
			),
			array(
				'field'     => 'image',
				'value'     => 5,
				'group'     => 'promo',
				'sequence'  => 0,
				'data_name' => 'assetID',
			),
			array(
				'field'     => 'image',
				'value'     => 100,
				'group'     => 'promo',
				'sequence'  => 0,
				'data_name' => 'width',
			),
			array(
				'field'     => 'image',
				'value'     => 150,
				'group'     => 'promo',
				'sequence'  => 0,
				'data_name' => 'height',
			),
			array(
				'field'     => 'body',
				'value'     => 'Welcome to the homepage',
				'group'     => null,
				'sequence'  => 0,
				'data_name' => null,
			),
		));

		$this->_loader = new ContentLoader(new Query($connection));
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage name collision on `myField`
	 */
	public function testDuplicateFieldNameException()
	{
		$page = $this->getMock('Message\Mothership\CMS\Page\Page');
		$page->id         = 1;
		$page->languageID = 'EN';
		$page->countryID  = null;

		$this->_loader->load($page);
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage name collision on `special`
	 */
	public function testFieldGroupNameCollisionException()
	{
		$page = $this->getMock('Message\Mothership\CMS\Page\Page');
		$page->id         = 2;
		$page->languageID = 'EN';
		$page->countryID  = null;

		$this->_loader->load($page);
	}

	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage Missing value name for multi-value field `fieldName`
	 */
	public function testMissingValueNameOnMultiValueFieldException()
	{
		$page = $this->getMock('Message\Mothership\CMS\Page\Page');
		$page->id         = 3;
		$page->languageID = 'EN';
		$page->countryID  = null;

		$this->_loader->load($page);
	}
	/**
	 * @expectedException        \RuntimeException
	 * @expectedExceptionMessage field `normalField` cannot have a sequence number
	 */
	public function testSequenceNumberOnUngroupedFieldException()
	{
		$page = $this->getMock('Message\Mothership\CMS\Page\Page');
		$page->id         = 4;
		$page->languageID = 'EN';
		$page->countryID  = null;

		$this->_loader->load($page);
	}

	public function testLoading()
	{
		$page = $this->getMock('Message\Mothership\CMS\Page\Page');
		$page->id         = 5;
		$page->languageID = 'EN';
		$page->countryID  = null;

		$content = $this->_loader->load($page);

		$this->assertSame('Message\Mothership\CMS\Page\Field\Field', get_class($content->normalField));
		$this->assertSame('Message\Mothership\CMS\Page\Field\Field', get_class($content->body));
		$this->assertSame('Some value', $content->normalField->__toString());
		$this->assertSame('Welcome to the homepage', $content->body->__toString());

		$this->assertInstanceOf('Message\Mothership\CMS\Page\Field\MultipleValueField', $content->multiValueField);
		$this->assertSame('This is part 1!', $content->multiValueField->part_1);
		$this->assertSame('This is part 2!', $content->multiValueField->part_2);

		$this->assertInstanceOf('Message\Mothership\CMS\Page\Field\Group', $content->callToAction);
		$this->assertSame('Message\Mothership\CMS\Page\Field\Field', get_class($content->callToAction->text));
		$this->assertInstanceOf('Message\Mothership\CMS\Page\Field\MultipleValueField', $content->callToAction->product);
		$this->assertSame('Message\Mothership\CMS\Page\Field\Field', get_class($content->callToAction->link));

		$this->assertSame('BUY BUY BUY BUY NOW!', $content->callToAction->text->__toString());
		$this->assertSame(8795, $content->callToAction->product->productID);
		$this->assertSame(120, $content->callToAction->product->colourID);
		$this->assertSame('/my-page', $content->callToAction->link->__toString());

		$this->assertInstanceOf('Message\Mothership\CMS\Page\Field\Repeatable', $content->promo);
		$this->assertCount(2, $content->promo);

		foreach ($content->promo as $key => $promo) {
			$this->assertInstanceOf('Message\Mothership\CMS\Page\Field\Group', $promo);
			$this->assertSame('Message\Mothership\CMS\Page\Field\Field', get_class($promo->title));
			$this->assertSame('Message\Mothership\CMS\Page\Field\Field', get_class($promo->description));
			$this->assertInstanceOf('Message\Mothership\CMS\Page\Field\MultipleValueField', $promo->image);

			switch ($key) {
				case 0:
					$this->assertSame('Check out our socks!', $promo->title->__toString());
					$this->assertSame('They are really warm and cosy. And cheap. And great. Buy them!', $promo->description->__toString());
					$this->assertSame(5, $promo->image->assetID);
					$this->assertSame(100, $promo->image->width);
					$this->assertSame(150, $promo->image->height);
					break;
				case 1:
					$this->assertSame('Check out our hats!', $promo->title->__toString());
					$this->assertSame('You can wear them. On your head.', $promo->description->__toString());
					$this->assertSame(1089, $promo->image->assetID);
					$this->assertSame(200, $promo->image->width);
					$this->assertSame(250, $promo->image->height);
					break;
			}
		}
	}
}