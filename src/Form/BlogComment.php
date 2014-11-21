<?php

namespace Message\Mothership\CMS\Form;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

class BlogComment extends Form\AbstractType
{
	public function getName()
	{
		return 'ms_blog_comment';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'text', [
			'label' => 'ms.cms.blog_comment.form.name',
			'constraints' => [
				new Constraints\NotBlank
			],
		]);

		$builder->add('email', 'email', [
			'label' => 'ms.cms.blog_comment.form.email',
			'constraints' => [
				new Constraints\NotBlank,
				new Constraints\Email
			],
		]);

		$builder->add($builder->create('website', 'text', [
			'label' => 'ms.cms.blog_comment.form.website',
			'constraints' => [
				new Constraints\Url,
			],
		])->addModelTransformer(new DataTransform\Url));

		$builder->add('comment', 'textarea', [
			'label' => 'ms.cms.blog_comment.form.comment',
			'constraints' => [
				new Constraints\NotBlank,
			],
		]);

		$builder->add('captcha', 'captcha', [
			'label' => 'ms.cms.blog_comment.form.captcha_prefix',
			'constraints' => [
				new Constraints\NotBlank
			]
		]);
	}
}