<?php

namespace Message\Mothership\CMS\Field\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Message\Cog\ValueObject\Slug as SlugObject;

class Slug extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data-slug' => new SlugObject(array())
		));
	}

	public function getParent()
	{
		return 'text';
	}

	public function getName()
	{
		return 'slug';
	}
}