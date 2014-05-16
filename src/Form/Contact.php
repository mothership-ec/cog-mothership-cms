<?php

namespace Message\Mothership\CMS\Form;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

class Contact extends Form\AbstractType
{
	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'text', [
			'label' => 'Your name',
			'constraints' => [
				new Constraints\NotBlank,
			],
		]);
		$builder->add('email', 'email', [
			'label' => 'Your email',
			'constraints' => [
				new Constraints\NotBlank
			],
		]);

		$builder->add('captcha', 'captcha');

		$builder->add('message', 'textarea', [
			'label' => 'Your message',
			'constraints' => [
				new Constraints\NotBlank,
			]
		]);
	}

	public function getName()
	{
		return 'ms_cms_contact';
	}
}