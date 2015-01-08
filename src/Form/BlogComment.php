<?php

namespace Message\Mothership\CMS\Form;

use Message\Cog\Form\Extension\Core\DataTransform;
use Message\User;
use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;

/**
 * Class BlogComment
 * @package Message\Mothership\CMS\Form
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Form for submitting comments
 */
class BlogComment extends Form\AbstractType
{
	/**
	 * @var \Message\User\UserInterface
	 */
	private $_user;

	public function __construct(User\UserInterface $user)
	{
		$this->_user = $user;
	}

	/**
	 * {@inheritDoc}
	 */
	public function getName()
	{
		return 'ms_blog_comment';
	}

	/**
	 * @param Form\FormBuilderInterface $builder
	 * @param array $options
	 */
	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		$builder->add('name', 'text', [
			'label' => 'ms.cms.blog_comment.form.name',
			'constraints' => [
				new Constraints\NotBlank
			],
			'data' => (!$this->_user instanceof User\AnonymousUser) ? $this->_user->getName() : null,
		]);

		$builder->add('email', 'email', [
			'label' => 'ms.cms.blog_comment.form.email',
			'constraints' => [
				new Constraints\NotBlank,
				new Constraints\Email
			],
			'data' => $this->_user->email,
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