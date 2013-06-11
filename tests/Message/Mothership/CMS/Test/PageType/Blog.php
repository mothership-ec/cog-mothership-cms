<?php

namespace Message\Mothership\CMS\Test\PageType;

use Message\Mothership\CMS\PageType\PageTypeInterface;

class Blog implements PageTypeInterface
{
	public function getName()
	{
		return 'blog';
	}

	public function getDisplayName()
	{
		return 'Blog';
	}

	public function getDescription()
	{
		return 'A blog page type, for use when unit testing.';
	}

	public function allowChildren()
	{
		return false;
	}

	public function getViewReference()
	{
		'::View:PageTypes:Blog';
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
			->setDescription('These are promotions to make people buy your stuff!')
			->setRepeatable(true, 3, 6)
			->add(new \Message\Mothership\CMS\Field\Type\Text('title', 'Promotion title'))
			->add(new \Message\Mothership\CMS\Field\Type\RichText('description', 'Promotion description'))
			->add(new \Message\Mothership\CMS\Field\Type\Link('url', 'Promotion link destination'))
			->add($factory->getField('file', 'file', 'Promotion background image')->setAllowedTypes(\Message\Mothership\FileManager\Type::IMAGE))
			->setTitleField('title'); // defaults to anything named 'title', if set

		// quick style
		$factory->addField('text', 'strapline', 'The catchy strapline')
			->setLocalisable(true);

		$factory->addGroup('promo', 'Promotions')
			->setRepeatable(true, 3, 6)
			->add($factory->getField('text', 'title', 'Title'), true)
			->add($factory->getField('richtext', 'description', 'Description')
					setallowedTypes(1)7->validator()
						->max(100)
						->matches('link')
			)
			->add($factory->getField('link', 'url', 'Link destination'))
			->add($factory->getField('file', 'image', 'Background image')->setAllowedTypes('image'));

		return $factory; // not necessary
	}
}