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
		ContentOptions::GUEST => 'Guests',
	];

	private $_permissionSelected = [
		ContentOptions::GUEST,
	];

	public function __construct(UserGroups $userGroups)
	{
		$this->_userGroups = $userGroups;
	}

	public function getName()
	{
		return 'blog';
	}

	public function getDisplayName()
	{
		return 'Blog';
	}

	public function getDescription()
	{
		return 'A blog post page';
	}

	public function allowChildren()
	{
		return false;
	}

	public function setFields(FieldFactory $factory)
	{
		$this->_addContentFields($factory);
		$this->_addCommentOptions($factory);

	}

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

	private function _setPermissions()
	{
		foreach ($this->_userGroups as $group) {
			$this->_permissionChoices[$group->getName()] = $group->getDisplayName();
			$this->_permissionSelected[] = $group->getName();
		}
	}
}