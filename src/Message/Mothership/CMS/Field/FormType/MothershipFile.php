<?php

namespace Message\Mothership\CMS\Field\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;

class MothershipFile extends AbstractType
{
	protected $_container;

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{

	}

	public function getParent()
	{
		return 'choice';
	}

	public function getName()
	{
		return 'ms_file';
	}
}