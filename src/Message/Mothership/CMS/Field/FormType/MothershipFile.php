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
		$resolver->setDefaults(array(
			//'data_class'    => 'Message\\Mothership\\FileManager\\File\\File',
			'allowed_types' => false,
		));
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars = array_replace($view->vars, array(
			'allowed_types' => $options['allowed_types'],
		));
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