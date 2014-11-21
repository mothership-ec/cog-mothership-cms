<?php

namespace Message\Mothership\CMS\Form;

use Message\Mothership\CMS\Blog\Statuses;

use Symfony\Component\Form;
use Symfony\Component\Validator\Constraints;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ManageComments
 * @package Message\Mothership\CMS\Form
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Form to display in admin panel, for managing comment statuses.
 */
class ManageComments extends Form\AbstractType
{
	private $_statuses;

	public function __construct(Statuses $statuses)
	{
		$this->_statuses = $statuses;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'ms_blog_manage_comments';
	}

	/**
	 * @param Form\FormBuilderInterface $builder
	 * @param array $options
	 */
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

	/**
	 * @param OptionsResolverInterface $resolver
	 */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
		$resolver->setRequired(['comments']);
		$resolver->setAllowedTypes([
			'comments' => ['Message\\Mothership\\CMS\\Blog\\CommentCollection'],
		]);
	}
}