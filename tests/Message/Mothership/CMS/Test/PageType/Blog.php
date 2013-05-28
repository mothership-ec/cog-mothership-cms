<?php

namespace Message\Mothership\CMS\Test\PageType;

use Message\Mothership\CMS\PageTypeInterface;

class Blog implements PageTypeInterface
{
	public function getName()
	{
		return 'blog';
	}

	public function getDescription()
	{
		return 'A blog page type, for use when unit testing.';
	}

	public function setFields(\Message\Mothership\CMS\PageTypeFieldFactory $factory)
	{
		// full style
		$strapline = new \Message\Mothership\CMS\Field\Type\Text('strapline', 'The catchy strapline');
		$strapline
			->setLocalisable(true);
		// set contextual help
		// set form field label - will we need to tell the field what it's label? see above
		// set validation
		//

		$group = new \Message\Mothership\CMS\Field\Group('promo', 'Promotions');
		$group
			->setRepeatable(true, 3, 6)
			->add(new \Message\Mothership\CMS\Field\Type\Text('title', 'Promotion title'), true)
			->add(new \Message\Mothership\CMS\Field\Type\RichText('description', 'Promotion description'))
			->add(new \Message\Mothership\CMS\Field\Type\Link('url', 'Promotion link destination'))
			->add(new \Message\Mothership\CMS\Field\Type\File('file', 'Promotion background image')); // some way to tell it to only show image files?

		return array($strapline, $group);

		// quick style
		$factory->addField('text', 'strapline', 'The catchy strapline')
			->setLocalisable(true);

		$factory->addGroup('promo', 'Promotions')
			->setRepeatable(true, 3, 6)
			->add($factory->getField('text', 'title', 'Title'), true)
			->add($factory->getField('richtext', 'description', 'Description'))
			->add($factory->getField('link', 'url', 'Link destination'))
			->add($factory->getField('file', 'image', 'Background image')->setAllowedTypes('image'));

		return $factory;
	}
}