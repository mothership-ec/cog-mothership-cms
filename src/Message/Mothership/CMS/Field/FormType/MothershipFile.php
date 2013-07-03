<?php

namespace Message\Mothership\CMS\Field\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class MothershipFile extends AbstractType
{
//	public function buildForm(FormBuilderInterface $builder, array $options)
//	{
//		$builder->add('file', 'hidden');
//	}

	public function getParent()
	{
		return 'hidden';
	}

	public function getName()
	{
		return 'ms-file';
	}

}