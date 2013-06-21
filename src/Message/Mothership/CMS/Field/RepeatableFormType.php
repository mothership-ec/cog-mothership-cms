<?php

namespace Message\Mothership\CMS\Field;

use Message\Cog\Form\Handler;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class RepeatableFormType extends AbstractType
{
	protected $_form;

	public function setForm(Handler $form)
	{
		$this->_form = $form;
	}

	public function buildForm(FormBuilderInterface $builder, array $options)
	{
		$builder->add($this->_form->getBuilder(), 'form');
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{

	}

	public function getName()
	{
		return $this->_form->getForm()->getConfig()->getOption('name');
	}
}