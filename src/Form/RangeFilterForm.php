<?php

namespace Message\Mothership\CMS\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RangeFilterForm extends AbstractType
{
	const MIN = 'min';
	const MAX = 'max';

	const MIN_LABEL = 'ms.cms.page_filter.min';
	const MAX_LABEL = 'ms.cms.page_filter.max';

	public function getName()
	{
		return 'cms_page_range_filter';
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add(self::MIN, 'choice', [
			'label'    => $options['min_label'],
			'choices'  => $options['choices'],
			'multiple' => false,
			'expanded' => $options['expanded'],
		]);

		$builder->add(self::MAX, 'choice', [
			'label'    => $options['max_label'],
			'choices'  => $options['choices'],
			'multiple' => false,
			'expanded' => $options['expanded'],
		]);
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'min_label' => self::MIN_LABEL,
			'max_label' => self::MAX_LABEL,
			'choices'   => [],
			'expanded'  => false,
		]);
	}
}