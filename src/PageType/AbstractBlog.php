<?php

namespace Message\Mothership\CMS\PageType;

use Message\Mothership\CMS\Blog\ContentOptions;

use Message\Cog\Field\Factory as FieldFactory;
use Message\User\Group\Collection as UserGroups;
use Message\Mothership\FileManager\File;

use Symfony\Component\Validator\Constraints;

/**
 * Abstract basis for blog posts. To implement, extend the class and set the view reference with the missing
 * `setViewReference()` method. Register the newly created page type to the `cms.page.types` service. Be sure
 * to remember to add the `user.groups` service to the constructor!
 *
 * Class Blog
 * @package Message\Mothership\CMS\PageType
 * @author Thomas Marchant <thomas@message.co.uk>
 */
abstract class AbstractBlog implements PageTypeInterface
{
	private $_userGroups;

	private $_permissionChoices = [
		ContentOptions::GUEST     => 'Guests',
		ContentOptions::LOGGED_IN => 'Logged in',
	];

	private $_permissionSelected = [
		ContentOptions::GUEST,
		ContentOptions::LOGGED_IN,
	];

	public function __construct(UserGroups $userGroups)
	{
		$this->_userGroups = $userGroups;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'blog';
	}

	/**
	 * @return string
	 */
	public function getDisplayName()
	{
		return 'Blog';
	}

	/**
	 * @return string
	 */
	public function getDescription()
	{
		return 'A blog post page';
	}

	/**
	 * @return bool
	 */
	public function allowChildren()
	{
		return false;
	}

	/**
	 * @param FieldFactory $factory
	 */
	public function setFields(FieldFactory $factory)
	{
		$this->_addContentFields($factory);
		$this->_addCommentOptions($factory);

	}

	/**
	 * @param FieldFactory $factory
	 */
	protected function _addContentFields(FieldFactory $factory)
	{
		$factory->add($factory->getField('richtext', 'body', 'ms.cms.page_type.blog.body')
			->setLocalisable(true)
			->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank,
				]
			]))
		;

		$imagesGroup = $factory->getGroup('images', 'ms.cms.page_type.blog.images')
			->setRepeatable(true)
		;

		$imagesGroup
			->add($factory->getField('file', 'image', 'ms.cms.page_type.blog.image')
				->setAllowedTypes(File\Type::IMAGE))
		;

		$imagesGroup
			->add($factory->getField('text', 'caption_heading', 'ms.cms.page_type.blog.caption_heading')->setFieldOptions([
				'constraints' => [
					new Constraints\NotBlank,
				],
			]))
		;

		$imagesGroup
			->add($factory->getField('richtext', 'caption', 'ms.cms.page_type.blog.caption'))
		;

		$factory->add($imagesGroup);

		$factory->addField('datetime', 'date', 'ms.cms.page_type.blog.date')->setFieldOptions([
			'empty_value' => new \DateTime()
		]);

		$factory->add($factory->getField('text', 'author', 'ms.cms.page_type.blog.author'))
		;

		$factory->add($factory->getField('richtext', 'description', 'ms.cms.page_type.blog.description'))
		;
	}

	/**
	 * @param FieldFactory $factory
	 */
	protected function _addCommentOptions(FieldFactory $factory)
	{
		$commentsGroup = $factory->getGroup('comments', 'ms.cms.page_type.blog.comments');
		$this->_setPermissions();

		$commentsGroup->add($factory->getField('choice', 'allow_comments', 'ms.cms.page_type.blog.comments_enable')->setFieldOptions([
			'expanded' => true,
			'choices'  => [
				'approve'  => 'ms.cms.page_type.blog.require_approval',
				'allow'    => 'ms.cms.page_type.blog.allow_comments',
				'disabled' => 'ms.cms.page_type.blog.disable_comments',
			],
			'data' => 'approve',
			'empty_value' => false,
		]))
		;

		$commentsGroup->add($factory->getField('multichoice', 'comment_permission', 'ms.cms.page_type.blog.comment_permission')->setFieldOptions([
			'expanded' => true,
			'choices'  => $this->_permissionChoices,
			'data'     => $this->_permissionSelected
		]))
		;

		$factory->add($commentsGroup);
	}

	/**
	 * Build list of permission choices from user groups
	 */
	private function _setPermissions()
	{
		foreach ($this->_userGroups as $group) {
			$this->_permissionChoices[$group->getName()] = $group->getDisplayName();
			$this->_permissionSelected[] = $group->getName();
		}
	}
}