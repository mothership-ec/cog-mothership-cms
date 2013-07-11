<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Mothership\CMS\Field;

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

	public function tabs()
	{
		$tabs = array(
			'Content' => $this->generateUrl('ms.cp.cms.edit.content', array(
				'pageID' => $this->get('http.request.master')->get('pageID')
			)),
			'Attributes' => $this->generateUrl('ms.cp.cms.edit.attributes', array(
				'pageID' => $this->get('http.request.master')->get('pageID')
			)),
			'Metadata' => $this->generateUrl('ms.cp.cms.edit.metadata', array(
				'pageID' => $this->get('http.request.master')->get('pageID')
			)),
		);

		$current = ucfirst(trim(strrchr($this->get('http.request.master')->get('_controller'), '::'), ':'));

		return $this->render('::edit/tabs', array(
			'tabs'    => $tabs,
			'current' => $current,
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
	public function content($pageID, $form = null)
	{
		$page    = $this->get('cms.page.loader')->getByID($pageID);

		if (!$page) {
			throw $this->createNotFoundException('Page ' . $pageID . ' does not exist', null, 404);
		}

		$content = $this->get('cms.page.content_loader')->load($page);
		$form    = $form ?: $this->_getContentForm($page, $content);

		return $this->_renderContentForm($page, $content, $form);
	}

	public function contentAction($pageID)
	{
		$page    = $this->get('cms.page.loader')->getByID($pageID);
		$content = $this->get('cms.page.content_loader')->load($page);
		$form    = $this->_getContentForm($page, $content);

		$this->_page = $page;

		// Redirect user back to the form if there are any errors
		if ($form->isValid()) {
			$content = $this->get('cms.page.content_edit')->updateContent($form->getFilteredData(), $content);

			if ($this->get('cms.page.content_edit')->save($page, $content)) {
				$this->addFlash('success', $this->trans('ms.cms.feedback.edit.content.success'));
			}
			else {
				$this->addFlash('error', $this->trans('ms.cms.feedback.edit.content.failure'));
			}
		}

		return $this->_renderContentForm($page, $content, $form);
	}

	/**
	 * Render the attributes form.
	 *
	 * @param int $pageID The page ID
	 */
	public function attributes($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		if (!$page) {
			throw $this->createNotFoundException('Page ' . $pageID . ' does not exist', null, 404);
		}

		$form = $this->_getAttibuteForm($page);

		return $this->render('::edit/attributes', array(
			'page' => $page,
			'form' => $form,
		));
	}

	/**
	 * Action to remove the slug from the history and add that slug to a new page
	 *
	 * @param  int 		$pageID 	The pageID of the page
	 * @param  string 	$slug   	The slug to remove from history and update
	 *                          	the given page
	 */
	public function forceSlugAction($pageID, $slug)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		$slugSegments = $page->slug->getSegments();
		$last  = array_pop($slugSegments);
		$slugSegments[] = $slug;
		$fullSlug = '/'.implode('/',$slugSegments);

		$this->get('cms.page.edit')->removeHistoricalSlug($fullSlug);
		$page = $this->_updateSlug($page, $slug);
		$this->addFlash('success', 'The url was successfully updated');

		return $this->redirectToReferer();
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
			$parent = $this->get('cms.page.loader')->getParent($page);
			$data['parent'] = isset($data['parent']) ? $data['parent'] : 0;
			// If the parentID != the submitted parent OR the parent is false
			// (as it's root) and the submitted parent is not 0 (root)
			if (($parent && $parent->id != $data['parent']) || (!$parent && $data['parent'] != 0)) {
				if ($this->get('cms.page.edit')->changeParent($pageID, $data['parent'])) {
					$this->addFlash('success', 'Parent successully changed');
				} else {
					$this->addFlash('error', 'The page could not be moved to a new position');
				}
			}

			if (!is_null($data['siblings']) && $data['siblings'] >= 0) {
				if ($this->get('cms.page.edit')->changeOrder($page, $data['siblings'])) {
					$this->addFlash('success', 'Page order successully changed');
				} else {
					$this->addFlash('error', 'The page could not be moved to a new position');
				}
			}

			$page = $this->_updateSlug($page, $data['slug']);

			$page->visibilitySearch 	= isset($data['visibility_search']);
			$page->visibilityMenu 		= isset($data['visibility_menu']);
			$page->visibilityAggregator = isset($data['visibility_aggregator']);
			$page->access 				= $data['access'] ?: 0;
			$page->accessGroups 		= $data['access_groups'];

			$page = $this->get('cms.page.edit')->save($page);
		}

		$this->addFlash('success', $this->trans('ms.cms.feedback.edit.attributes.success'));

		return $this->render('::edit/attributes', array(
			'page' => $page,
			'form' => $form,
		));
	}

	/**
	 * Render metadata form
	 *
	 * @param int $pageID
	 *
	 * @return Response
	 */
	public function metadata($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		if (!$page) {
			throw $this->createNotFoundException('Page ' . $pageID . ' does not exist', null, 404);
		}

		$form = $this->_getMetadataForm($page);

		return $this->render('::edit/metadata', array(
			'page' => $page,
			'form' => $form,
		));
	}

	/**
	 * Validate metadata and save to page
	 *
	 * @param int $pageID
	 *
	 * @return \Message\Cog\HTTP\RedirectResponse
	 */
	public function metadataAction($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$form = $this->_getMetadataForm($page);

		if ($form->isValid() && ($data = $form->getFilteredData())) {
			$page->metaTitle       = $data['metaTitle'];
			$page->metaDescription = $data['metaDescription'];
			$page->metaHtmlHead    = $data['metaHtmlHead'];
			$page->metaHtmlFoot    = $data['metaHtmlFoot'];

			$page = $this->get('cms.page.edit')->save($page);

			$this->addFlash('success', $this->trans('ms.cms.feedback.edit.attributes.success'));
		}

		return $this->render('::edit/metadata', array(
			'page' => $page,
			'form' => $form,
		));
	}

	/**
	 * Get content form
	 *
	 * @param Page $page
	 * @param Content $content
	 *
	 * @return mixed
	 */
	protected function _getContentForm(Page $page, Content $content = null)
	{
		if (!$content) {
			$content = $this->get('cms.page.content_loader')->load($page);
		}

		$form = $this->get('form')
			->setName('content-edit-content')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.cms.edit.content.action', array(
				'pageID' => $page->id,
			)));

		return $this->get('cms.field.form')->generate($form, $content);
	}

	protected function _renderContentForm(Page $page, Content $content, $form)
	{
		// Build array of repeatable groups & their fields for use in the view
		$repeatables = array();
		foreach ($content as $name => $contentPart) {
			if ($contentPart instanceof Field\RepeatableContainer) {
				$repeatables[$name] = array();
				foreach ($contentPart->getFields() as $field) {
					$repeatables[$name][] = $field->getName();
				}
			}
		}

		return $this->render('::edit/content', array(
			'page'        => $page,
			'content'     => $content,
			'form'        => $form,
			'repeatables' => $repeatables,
		));
	}

	protected function _getAttibuteForm(Page $page)
	{
		$parent = $this->get('cms.page.loader')->getParent($page);
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
				'access_groups'         => $page->accessGroups,
				'tags'                  => implode(', ', $page->tags),
				'parent'                => $parent ? $parent->id : 0,
				'siblings'              => '',
			));

		$form->add('slug', 'text', $this->trans('ms.cms.attributes.slug.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.attributes.slug.help'),
		))->val()->match('/^[a-z0-9\-]+$/');

		$form->add('visibility_menu', 'checkbox', $this->trans('ms.cms.attributes.visibility.menu.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.attributes.visibility.menu.help'),
		));
		$form->add('visibility_search', 'checkbox', $this->trans('ms.cms.attributes.visibility.search.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.attributes.visibility.search.help'),
		));
		$form->add('visibility_aggregator', 'checkbox', $this->trans('ms.cms.attributes.visibility.aggregator.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.attributes.visibility.aggregator.help'),
		));

		$form->add('access', 'choice', $this->trans('ms.cms.attributes.access.label'), array(
			'choices' => array(
				Authorisation::ACCESS_ALL        => $this->trans('ms.cms.attributes.access.options.all'),
				Authorisation::ACCESS_GUEST      => $this->trans('ms.cms.attributes.access.options.guest'),
				Authorisation::ACCESS_USER       => $this->trans('ms.cms.attributes.access.options.user'),
				Authorisation::ACCESS_USER_GROUP => $this->trans('ms.cms.attributes.access.options.group'),
			),
			'attr' => array('data-help-key' => 'ms.cms.attributes.access.help'),
		));

		$form->add('access_groups', 'choice', $this->trans('ms.cms.attributes.access_groups.label'), array(
			'attr'     => array('data-help-key' => 'ms.cms.attributes.access_groups.help'),
			'choices'  => $this->get('user.groups')->flatten(),
			'multiple' => true,
		))->val()->optional();

		$siblings = $this->get('cms.page.loader')->getSiblings($page);
		$siblingChoices = array();
		$siblingChoices[0] = 'Move to top';
		if ($siblings) {
			foreach ($siblings as $s) {
				$siblingChoices[$s->id] = $s->title;
			}
		}
		$form->add('siblings', 'choice', $this->trans('ms.cms.attributes.order.label'), array(
			'attr'    => array('data-help-key' => 'ms.cms.attributes.order.search.help'),
			'choices' => $siblingChoices,
		))->val()->optional();

		$parents = $this->get('cms.page.loader')->getAll();

		$choices = array();
		foreach ($parents as $p) {
			$spaces = str_repeat("--", $p->depth + 1);
			// don't display the option to move it to a page which doesn't allow children
			if (!$p->type->allowChildren()) {
				continue;
			}
			// Ignore any children pages of itself - we cannot go inside itself
			if ($p->left > $page->left && $p->right < $page->right) {
				continue;
			}

			$choices[$p->id] = $spaces.' '.$p->title;
		}
		$form->add('parent', 'choice', $this->trans('ms.cms.attributes.parent.label'), array(
			'attr'        => array('data-help-key' => 'ms.cms.attributes.parent.help'),
			'choices'     => $choices,
			'empty_value' => 'Top level',
		))->val()->optional();

		return $form;
	}

	/**
	 * Get form for metadata section of edit page
	 *
	 * @param Page $page
	 * @param Content $content
	 *
	 * @return \Message\Cog\Form\Handler
	 */
	protected function _getMetadataForm(Page $page)
	{
		$form = $this->get('form')
			->setName('content-edit-metadata')
			->setAction($this->generateUrl('ms.cp.cms.edit.metadata.action', array(
				'pageID' => $page->id
			)))
			->setMethod('post')
			->setDefaultValues(array(
				'metaTitle'       => $page->metaTitle,
				'metaDescription' => $page->metaDescription,
				'metaHtmlHead'    => $page->metaHtmlHead,
				'metaHtmlFoot'    => $page->metaHtmlFoot,
			));

		$form->add('metaTitle', 'text', $this->trans('ms.cms.metadata.title.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.metadata.title.help')
		))->val()
			->optional()
			->maxLength(255);

		$form->add('metaDescription', 'textarea', $this->trans('ms.cms.metadata.description.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.metadata.description.help')
		))->val()
			->optional();

		$form->add('metaHtmlHead', 'textarea', $this->trans('ms.cms.metadata.htmlHead.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.metadata.htmlHead.help')
		))->val()
			->optional();

		$form->add('metaHtmlFoot', 'textarea', $this->trans('ms.cms.metadata.htmlFoot.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.metadata.htmlFoot.help')
		))->val()
			->optional();

		$form->add('test', 'slug');
		
		return $form;
	}

	/**
	 * Check to see whether we can update a slug or not. This also checks the
	 * slug history and other pages and sets up the feedback to allow actions
	 * to replace the old slug.
	 *
	 * @param  Page   	$page
	 * @param  string 	$newSlug [description]
	 *
	 * @return Page 	$page
	 */
	protected function _updateSlug(Page $page, $newSlug)
	{
		// Flag as to whether to update the slug
		$update = true;

		$slugSegments = $page->slug->getSegments();
		$last  = array_pop($slugSegments);
		$slugSegments[] = $newSlug;
		$slug = '/'.implode('/',$slugSegments);
		$checkSlug = $this->get('cms.page.loader')->getBySlug($slug, false);

		// If not slug has been found, we need to check the history too
		if (!$checkSlug) {
			// Check for the slug historicaly and show deleted ones too
			$historicalSlug = $this->get('cms.page.loader')
				->includeDeleted(true)
				->getBySlug($slug, true);

			// If there is a page returned and it's not this page then offer
			// a link to remove the slug from history and use it anyway
			if ($historicalSlug && $historicalSlug->id != $page->id) {
				// If it's been deleted then offer a differnt message that a non deleted one
				if (!is_null($historicalSlug->authorship->deletedAt())) {
					$this->addFlash('error', 'The url <code>'.$slug.'</code> is saved against a page which has been deleted. Would you like to use this url anyway? <a href="'.$this->generateUrl('ms.cp.cms.edit.attributes.slug.force', array('pageID' => $page->id,'slug' => $newSlug)).'">Yes please Mothership you clever thing!</a>');
				}
				else {
					$this->addFlash('error', 'The url <code>'.$slug.'</code> has been used in the past and is being redirected to <a href="'.$this->generateUrl('ms.cp.cms.edit.attributes', array('pageID' => $historicalSlug->id)).'">'.$historicalSlug->title.'</a>. Would you like to use this url anyway? <a href="'.$this->generateUrl('ms.cp.cms.edit.attributes.slug.force', array('pageID' => $page->id,'slug' => $newSlug)).'">Yes please!</a>');
				}

				// We shouldn't update the slug as we need action
				$update = false;
			}
		}

		if ($checkSlug && $checkSlug->id != $page->id) {
			$this->addFlash('error', 'The url <code>'.$checkSlug->slug->getFull().'</code> is already in use on the page <a href="'.$this->generateUrl('ms.cp.cms.edit.attributes', array('pageID' => $checkSlug->id)).'">'.$checkSlug->title.'</a>');
			// We shouldn't update the slug as we need action
			$update = false;
		}

		// If the slug has changed then update the slug
		if ($update && $page->slug->getLastSegment() != $newSlug) {
			$this->get('cms.page.edit')->removeHistoricalSlug($slug);
			$page = $this->get('cms.page.edit')->updateSlug($page, $newSlug);
		}

		// return the updated or unchanged page
		return $page;
	}
}