<?php

namespace Message\Mothership\CMS\Test\Form;

use Message\Mothership\CMS\Field\Form;
use Message\Mothership\CMS\Field\Factory;
use Message\Cog\Form\Handler;

class FormTest extends \PHPUnit_Framework_TestCase
{
	public function testMakingForm()
	{
		$factory = new Factory();

		$factory->add($factory->getField('text', 'strapline', 'The catchy strapline')
		    ->setLocalisable(true));

		$factory->addGroup('promo', 'Promotions')
		    ->setRepeatable(true, 3, 6)
		    ->add($factory->getField('text', 'title', 'Title'))
		    ->add($factory->getField('richtext', 'description', 'Description'))
		    ->add($factory->getField('link', 'url', 'Link destination'))
		    ->add($factory->getField('file', 'image', 'Background image'));

		$factory->addField('integer', 'number_of_things', 'Number of things');

		$factory->addGroup('mygroup', 'Not repeatable group')
		    ->add($factory->getField('text', 'name', 'Name'))
		    ->add($factory->getField('selectmenu', 'colour', 'Favourite colour')
		        ->setOptions(array(
		            'Red',
		            'Blue',
		            'Green',
		        )));

		$handler = new Handler(Services::get());
		$form = new Form($factory, $handler);

		$form->generateForm();
	}
}