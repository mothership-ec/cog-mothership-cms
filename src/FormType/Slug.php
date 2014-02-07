<?php

namespace Message\Mothership\CMS\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Message\Mothership\CMS\Field\FormType\DataTransform\Slug as SlugTransform;

class Slug extends AbstractType
{
	public function getParent()
	{
		return 'text';
	}

	public function getName()
	{
		return 'ms_slug';
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->addModelTransformer(new SlugTransform);
	}
}