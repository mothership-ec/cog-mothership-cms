<?php

namespace Message\Mothership\CMS\Form;

use Message\Mothership\CMS\Blog\Statuses;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ManageComments extends Form\AbstractType
{
	private $_statuses;

	public function __construct(Statuses $statuses)
	{
		$this->_statuses = $statuses;
	}

	public function getName()
	{
		return 'ms_blog_manage_comments';
	}

	public function buildForm(Form\FormBuilderInterface $builder, array $options)
	{
		foreach ($options['comments'] as $comment) {
			$builder->add('comment_' . $comment->getID(), 'choice', [
				'multiple'    => false,
				'expanded'    => true,
				'choices'     => $this->_statuses->getStatuses(),
				'data'        => $comment->getStatus(),
				'constraints' => [
					new Constraints\NotBlank
				],
			]);
		}
	}

	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(['comments']);
		$resolver->setAllowedTypes([
			'comments' => ['Message\\Mothership\\CMS\\Blog\\CommentCollection'],
		]);
	}
}