<?php

namespace Message\Mothership\CMS\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class CmsLink extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('scope', 'hidden', [
			'data' => 'cms',
		]);
		$builder->add('target', 'choice', [
			'multiple' => false,
			'expanded' => false,
			'label'    => (!empty($options['label'])) ? $options['label'] : 'Link',
			'choices'  => $options['choices'],
			'data'     => (!empty($options['data'])) ? $options['data'] : null,
		]);
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
		return 'cms_link';
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'choices'  => [],
			'multiple' => false,
			'expanded' => false,
		));
	}
}