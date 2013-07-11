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

	public function getName()
	{
		return 'slug';
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildView(FormView $view, FormInterface $form, array $options)
	{
		if (isset($options['data_slug']) && (!$options['data_slug'] instanceof SlugObject)) {
			throw new \InvalidArgumentException('\'data_slug\' option must be an instance of \\Message\\Cog\\ValueObject\\Slug');
		}
		elseif (isset($options['data_slug'])) {
			$segments = $options['data_slug']->getSegments();
			array_pop($segments);
			$preSlug = implode('/', $segments) . '/';
			$value = $options['data_slug']->getLastSegment();
		}

		$view->vars = array_replace($view->vars, array(
			'pre_slug'  => isset($preSlug) ? $preSlug : $this->_preSlug,
			'value'     => isset($value) ? $value : $this->_value,
		));
	}
}