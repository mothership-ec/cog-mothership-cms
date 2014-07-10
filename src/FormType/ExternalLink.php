<?php

namespace Message\Mothership\CMS\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ExternalLink extends AbstractType
{
	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add('scope', 'hidden', [
			'data' => 'external',
		]);
		$builder->add('target', 'url', [
			'label'    => $options['label'],
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
		return 'external_link';
	}
}