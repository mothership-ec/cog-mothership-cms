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

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'cms_page_range_filter';
	}

	/**
	 * {@inheritDoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add(self::MIN, 'choice', [
			'label'       => $options['min_label'],
			'choices'     => $options['choices'],
			'multiple'    => false,
			'expanded'    => $options['expanded'],
			'empty_value' => $options['min_placeholder']
		]);

		$builder->add(self::MAX, 'choice', [
			'label'       => $options['max_label'],
			'choices'     => $options['choices'],
			'multiple'    => false,
			'expanded'    => $options['expanded'],
			'empty_value' => $options['max_placeholder']
		]);
	}

	/**
	 * {@inheritDoc}
	 *
	 * Options:
	 * 	- `min_label`         Label for minimum selector field
	 *  - `max_label`         Label for maximum selector field
	 *  - `min_placeholder`   Placeholder for minimum selector field
	 *  - `max_placeholder`   Placeholder for maximum selector field
	 *  - `choices`           Choices to be assigned to both selector fields
	 *  - `expanded`          Set as `false` for select drop down, and `true` for radio buttons
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setDefaults([
			'min_label'       => self::MIN_LABEL,
			'max_label'       => self::MAX_LABEL,
			'min_placeholder' => null,
			'max_placeholder' => null,
			'choices'         => [],
			'expanded'        => false,
		]);
	}
}