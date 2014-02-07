<?php

namespace Message\Mothership\CMS\FormType;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Two choice menus that have some sort of inherit link.
 *
 * @todo Figure out how to map the names to the values
 */
class LinkedChoice extends AbstractType
{
	protected $_choiceGroups;

	public function __construct(array $choiceGroups)
	{
		$this->_groups = $choiceGroups;
	}

	/**
	 * {@inheritdoc}
	 */
	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		foreach ($this->_groups as $name => $choices) {
			$choices = array_merge(array('none' => 'None'), $choices);
			$builder->add($name, 'choice', array(
				'choices'     => $choices,
				'empty_value' => 'Please select...',
			));
		}
	}

	public function getName()
	{
		return 'linked_choice';
	}
}