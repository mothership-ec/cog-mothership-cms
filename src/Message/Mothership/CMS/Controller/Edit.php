<?php

namespace Message\Mothership\CMS\Controller;

use Message\Mothership\CMS\Field\Form;
use Message\Mothership\CMS\Field\Factory;
use Message\Mothership\CMS\Field\RepeatableContainer;
use Message\Mothership\CMS\Page\Authorisation;

class Edit extends \Message\Cog\Controller\Controller
{
	/**
	 * Index for editing, this just redirects to the content edit screen.
	 *
	 * @param int $pageID The page ID
	 */
	public function index($pageID)
	{
		return $this->redirectToRoute('ms.cp.cms.edit.content', array(
			'pageID' => $pageID,
		));
	}

	/**
	 * POST action for updating the page's title.
	 *
	 * @param int $pageID The page ID
	 */
	public function updateTitle($pageID)
	{
		if (!$data = $this->get('request')->request->get('edit')) {
			return $this->redirect($this->get('request')->headers->get('referer'));
		}

		$page = $this->get('cms.page.loader')->getByID($pageID);
		$page->title = $data['title'];

		$page = $this->get('cms.page.edit')->save($page);

		return $this->redirectToRoute('ms.cp.cms.edit', array(
			'pageID' => $page->id,
		));
	}

	/**
	 * Render the content form.
	 *
	 * @param int $pageID The page ID
	 */
	public function content($pageID)
	{
		$page    = $this->get('cms.page.loader')->getByID($pageID);
		$content = $this->get('cms.page.content_loader')->load($page);
		$form    = $this->get('form')
			->setMethod('POST'); // TODO: set action
		$form    = $this->get('cms.field.form')->generate($form, $content);

		// Build array of repeatable groups & their fields for use in the view
		$repeatables = array();
		foreach ($content as $name => $contentPart) {
			if ($contentPart instanceof RepeatableContainer) {
				$repeatables[$name] = array();
				foreach ($contentPart->get(0)->getFields() as $field) {
					$repeatables[$name][] = $field->getName();
				}
			}
		}

		return $this->render('::edit/content', array(
			'page'        => $page,
			'form'        => $form,
			'content'     => $content,
			'repeatables' => $repeatables,
		));
	}

	/**
	 * Render the attributes form.
	 *
	 * @param int $pageID The page ID
	 */
	public function attributes($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		$form = $this->get('form')
			->setName('attributes')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.cms.edit.attributes.action', array(
				'pageID' => $pageID,
			)))
			->setDefaultValues(array(
				'slug'                  => $page->slug->getLastSegment(),
				'visibility_menu'       => $page->visibilityMenu,
				'visibility_search'     => $page->visibilitySearch,
				'visibility_aggregator' => $page->visibilityAggregator,
				'access'                => $page->access,
				'access_groups'         => $page->accessGroups,
				'tags'                  => implode(', ', $page->tags),
			));

		$form->add('slug', 'text', $this->trans('ms.cms.attributes.slug.label'))
			->val()->match('^/[a-z0-9\-]+/$');

		$form->add('visibility_menu', 'checkbox', $this->trans('ms.cms.attributes.visibility.menu.label'));
		$form->add('visibility_search', 'checkbox', $this->trans('ms.cms.attributes.visibility.search.label'));
		$form->add('visibility_aggregator', 'checkbox', $this->trans('ms.cms.attributes.visibility.aggregator.label'));

		$form->add('access', 'choice', $this->trans('ms.cms.attributes.access.label'), array('choices' => array(
			Authorisation::ACCESS_ALL        => $this->trans('ms.cms.attributes.access.options.all'),
			Authorisation::ACCESS_GUEST      => $this->trans('ms.cms.attributes.access.options.guest'),
			Authorisation::ACCESS_USER       => $this->trans('ms.cms.attributes.access.options.user'),
			Authorisation::ACCESS_USER_GROUP => $this->trans('ms.cms.attributes.access.options.group'),
		)));

		$form->add('access_groups', 'choice', $this->trans('ms.cms.attributes.access_groups.label'), array(
			'choices'  => $this->get('user.groups')->flatten(),
			'multiple' => true,
		))->val()->optional();

		$form->add('tags', 'textarea', $this->trans('ms.cms.attributes.tags.label'))
			->val()->optional();

		return $this->render('::edit/attributes', array(
			'page' => $page,
			'form' => $form,
		));
	}

	/**
	 * Render the metadata form.
	 *
	 * @param int $pageID The page ID
	 */
	public function metadata($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		return $this->render('::edit/metadata', array(
			'page' => $page,
		));
	}
}