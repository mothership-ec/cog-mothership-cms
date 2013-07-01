<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Mothership\CMS\Page\Authorisation;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Content;
use Message\Mothership\CMS\Field\Form;
use Message\Mothership\CMS\Field\Factory;
use Message\Mothership\CMS\Field\RepeatableContainer;
use Message\Cog\ValueObject\Slug;

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
			return $this->redirectToReferer();
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
		$form    = $this->_getContentForm($page, $content);

		// Build array of repeatable groups & their fields for use in the view
		$repeatables = array();
		foreach ($content as $name => $contentPart) {
			if ($contentPart instanceof RepeatableContainer) {
				$repeatables[$name] = array();
				foreach ($contentPart->getFields() as $field) {
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

	public function contentAction($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$form = $this->_getContentForm($page);

		// Redirect user back to the form if there are any errors
		if (!$form->isValid()) {
			return $this->redirectToReferer();
		}

		$data = $form->getFilteredData();

		var_dump($form);exit;
	}

	/**
	 * Render the attributes form.
	 *
	 * @param int $pageID The page ID
	 */
	public function attributes($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$form = $this->_getAttibuteForm($page);

		return $this->render('::edit/attributes', array(
			'page' => $page,
			'form' => $form,
		));
	}

	protected function _getAttibuteForm(Page $page)
	{
		$form = $this->get('form')
			->setName('attributes')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.cms.edit.attributes.action', array(
				'pageID' => $page->id,
			)))
			->setDefaultValues(array(
				'slug'                  => $page->slug->getLastSegment(),
				'visibility_menu'       => $page->visibilityMenu,
				'visibility_search'     => $page->visibilitySearch,
				'visibility_aggregator' => $page->visibilityAggregator,
				'access'                => $page->access,
				'access_groups'         => array_keys($page->accessGroups),
				'tags'                  => implode(', ', $page->tags),
			));

		$form->add('slug', 'text', $this->trans('ms.cms.attributes.slug.label'))
			->val()->match('/^[a-z0-9\-]+$/');

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

		return $form;
	}

	/**
	 * Validate the input and update the attributes
	 *
	 * @todo 	We need a way to check that the slug is still in the same position
	 *        	As when we add moving of pages the slug might be different *or*
	 *        	already exist in that new section.
	 *
	 * @param  	int 	$pageID 	id of the Page object to be loaded and updated
	 */
	public function attributesAction($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$form = $this->_getAttibuteForm($page);

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$checkSlug = $this->get('cms.page.loader')->getBySlug($data['slug'], true);
			// Check the slug doesn't already exist and that there isn't a
			// historical slug of the same name
			if ($checkSlug && (is_array($checkSlug) || $checkSlug->id != $pageID)) {
				$this->addFlash('error', 'This slug already exists');

				return $this->redirectToReferer();
			}

			// If the slug has changed then create a new slug object
			if ($page->slug->getLastSegment() != $data['slug']) {
				$page = $this->get('cms.page.edit')->updateSlug($page, $data['slug']);
			}

			$page->visibilitySearch 	= isset($data['visibility_search']);
			$page->visibilityMenu 		= isset($data['visibility_menu']);
			$page->visibilityAggregator = isset($data['visibility_aggregator']);
			$page->access 				= $data['access'] ?: 0;
			$page->accessGroups 		= $data['access_groups'];
			$page = $this->get('cms.page.edit')->save($page);

		}
		$this->addFlash('success', $this->trans('ms.cms.feedback.edit.attributes.success'));

		return $this->redirectToRoute('ms.cp.cms.edit.attributes.', array('pageID' => $page->id));

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

	protected function _getContentForm(Page $page, Content $content = null)
	{
		if (!$content) {
			$content = $this->get('cms.page.content_loader')->load($page);
		}

		$form = $this->get('form')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.cms.edit.content.action', array(
				'pageID' => $page->id,
			)));

		return $this->get('cms.field.form')->generate($form, $content);
	}
}