<?php

namespace Message\Mothership\CMS\PageType;

use Message\Cog\Field\Factory as FieldFactory;
use Message\Mothership\FileManager\File;

use Symfony\Component\Validator\Constraints;

abstract class Blog implements PageTypeInterface
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
		return 'A blog post page';
	}

	public function allowChildren()
	{
		return false;
	}

	public function setFields(FieldFactory $factory)
	{
		$factory->add($factory->getField('richtext', 'body', 'Post body')
			->setLocalisable(true)
			->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank,
				]
			]))
		;

		$imagesGroup = $factory->getGroup('images', 'Images')
			->setRepeatable(true)
		;

		$imagesGroup
			->add($factory->getField('file', 'image', 'Image')
				->setAllowedTypes(File\Type::IMAGE))
		;

		$imagesGroup
			->add($factory->getField('text', 'caption_heading', 'Caption heading')->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank,
				],
			]))
		;

		$imagesGroup
			->add($factory->getField('richtext', 'caption', 'Caption'))
		;

		$factory->add($imagesGroup);

		$factory->addField('datetime', 'date', 'Display date')->setFieldOptions([
			'empty_value' => new \DateTime()
		]);

		$factory->add($factory->getField('text', 'author', 'Author override'))
		;

		$factory->add($factory->getField('richtext', 'description', 'Description for listings'))
		;
	}
}