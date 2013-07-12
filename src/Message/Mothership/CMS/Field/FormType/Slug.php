<?php

namespace Message\Mothership\CMS\Field\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Message\Cog\ValueObject\Slug as SlugObject;

class Slug extends AbstractType
{
	protected $_preSlug = '/';
	protected $_value = '';

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults(array(
//			'data_class'    => 'Message\\Cog\\ValueObject\\Slug',
//			'value'         => new SlugObject(array()),
			'data_slug'     => new SlugObject(array())
		));
	}

	public function getParent()
	{
		return 'choice';
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
		$slug = $options['data_slug'];

		if ($slug && (!$slug instanceof SlugObject)) {
			throw new \InvalidArgumentException('The \'data_slug\' option must be an instance of \\Message\\Cog\\ValueObject\\Slug');
		}
		elseif ($slug) {
			$segments = $slug->getSegments();
			array_pop($segments);
			$preSlug = '/' . trim(implode('/', $segments), '/') . '/';
			$value = $slug->getLastSegment();
		}

		$view->vars = array_replace($view->vars, array(
			'pre_slug_string'  => isset($preSlug) ? $preSlug : $this->_preSlug,
			'pre_slug_value'   => isset($segments) ? $segments : array(),
			'value'     => isset($value) ? $value : $this->_value,
		));
	}
}