<?php

namespace Message\Mothership\CMS\Form;

use Symfony\Component\Form;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;
use Message\Cog\Localisation\Translator;

class Contact extends Form\AbstractType
{
	protected $_trans;

	public function __construct(Translator $trans)
	{
		$this->_trans = $trans;
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'text', [
			'label' => $this->_trans->trans('ms.cms.contact.form.name'),
			'constraints' => [
				new Constraints\NotBlank,
			],
		]);
		$builder->add('email', 'email', [
			'label' => $this->_trans->trans('ms.cms.contact.form.email'),
			'constraints' => [
				new Constraints\NotBlank
			],
		]);

		$builder->add('message', 'textarea', [
			'label' => $this->_trans->trans('ms.cms.contact.form.message'),
			'constraints' => [
				new Constraints\NotBlank,
			]
		]);

		$builder->add('captcha', 'captcha', [
			'label' => $this->_trans->trans('ms.cms.contact.form.captcha-prefix'),
		]);
	}

	public function getName()
	{
		return 'ms_cms_contact';
	}
}