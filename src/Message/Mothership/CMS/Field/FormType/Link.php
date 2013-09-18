<?php

namespace Message\Mothership\CMS\Field\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class Link extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('scope', 'hidden');
		$builder->add('target', 'url', array('label' => $options['label'])); // Copy label to "target" field
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars = array_replace($view->vars, array(
			'label' => false, // Hide label for this field group (we want the label for "target" only)
		));
	}

	public function getName()
	{
		return 'link';
	}
}