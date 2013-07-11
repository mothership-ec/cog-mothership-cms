<?php

namespace Message\Mothership\CMS\Field\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Message\Cog\ValueObject\Slug as SlugObject;

class Slug extends AbstractType
{
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
			'data_slug' => new SlugObject(array())
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

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		$view->vars = array_replace($view->vars, array(
			'data_slug' => $options['data_slug'],
		));
	}
}