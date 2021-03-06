<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Cog\Field;
use Message\Cog\DB\NestedSetException;

use Message\Mothership\CMS\Page\Authorisation;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Content;
use Message\Mothership\CMS\Page\Exception\PageEditException;
use Message\Mothership\CMS\Page\Exception\SlugUpdateException;

use Message\Cog\ValueObject\Slug;
use Message\Mothership\FileManager\File;
use Message\Mothership\CMS\Page\Exception\InvalidSlugException;

class Edit extends \Message\Cog\Controller\Controller
{
	protected $_page;

	/**
	 * Index for editing, this just redirects to the content edit screen.
	 *
	 * @param int $pageID The page ID
	 *
	 * @return Response
	 */
	public function index($pageID)
	{
		return $this->redirectToRoute('ms.cp.cms.edit.content', array(
			'pageID' => $pageID,
		));
	}

	public function tabs()
	{
		// This is introducing an inefficiency, loading the page and content in the menu, knowing they will be loaded
		// later in another subrequest. We should consider a better way to add the 'Comments' tab to the appropriate menu
		// in the future.
		$page = $this->get('cms.page.loader')->getByID($this->get('http.request.master')->get('pageID'));
		$content = $this->get('cms.page.content_loader')->load($page);

		$tabs = [];
		$tabs['Content'] = $this->generateUrl('ms.cp.cms.edit.content', [
			'pageID' => $page->id
		]);
		$tabs['Attributes'] = $this->generateUrl('ms.cp.cms.edit.attributes', [
			'pageID' => $page->id
		]);

		if ($this->get('cms.blog.content_validator')->isValid($content, true)) {
			$tabs['Comments'] = $this->generateUrl('ms.cp.cms.edit.comments', [
				'pageID' => $page->id
			]);
		}

		$tabs['Metadata'] = $this->generateUrl('ms.cp.cms.edit.metadata', [
			'pageID' => $page->id
		]);

		$current = ucfirst(trim(strrchr($this->get('http.request.master')->get('_controller'), '::'), ':'));

		return $this->render('Message:Mothership:CMS::edit/tabs', array(
			'tabs'    => $tabs,
			'current' => $current,
		));
	}

	/**
	 * POST action for updating the page's title.
	 *
	 * @param int $pageID The page ID
	 *
	 * @return Response
	 */
	public function updateTitle($pageID)
	{
		if (!$data = $this->get('request')->request->get('edit')) {
			return $this->redirectToReferer();
		}

		$page = $this->get('cms.page.loader')->getByID($pageID);
		$page->title = $data['title'];

		$page = $this->get('cms.page.edit')->save($page);

		$this->addFlash('success', $this->trans('ms.cms.feedback.edit.title.success', array(
			'%title%' => $page->title,
		)));

		return $this->redirectToRoute('ms.cp.cms.edit', array(
			'pageID' => $page->id,
		));
	}

	/**
	 * Render the content form.
	 *
	 * @param int $pageID The page ID
	 *
	 * @return Response
	 */
	public function content($pageID, $form = null)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

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

		$form->handleRequest();

		// Redirect user back to the form if there are any errors
		if ($form->isValid()) {
			$content = $this->get('cms.page.content_edit')->updateContent($form->getData(), $content);
			if ($this->get('cms.page.content_edit')->save($page, $content)) {
				$this->addFlash('success', $this->trans('ms.cms.feedback.edit.content.success'));
			} else {
				$this->addFlash('error', $this->trans('ms.cms.feedback.edit.content.failure'));
			}
		}

		return $this->redirectToReferer();
	}

	/**
	 * Render the attributes form.
	 *
	 * @param int $pageID The page ID
	 *
	 * @return Response
	 */
	public function attributes($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		if (!$page) {
			throw $this->createNotFoundException(
				$this->trans('ms.cms.feedback.general.failure.non-existing-page', array('%pageID%' => $page->id)),
				null,
				404
			);
		}

		$form = $this->_getAttributeForm($page);

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
	 *
	 * @return Response
	 */
	public function forceSlugAction($pageID, $slug)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		$slugSegments = $page->slug->getSegments();
		$last  = array_pop($slugSegments);
		$slugSegments[] = $slug;
		$fullSlug = '/'.implode('/',$slugSegments);

		$this->get('cms.page.edit')->removeHistoricalSlug($fullSlug);
		$page = $this->_updateSlug($page, new Slug($slugSegments));
		$this->addFlash('success', $this->trans('ms.cms.feedback.force-slug.success'));

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
	 *
	 * @return Response
	 */
	public function attributesAction($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$form = $this->_getAttributeForm($page);

		if ($form->isValid() && $data = $form->getFilteredData()) {
			$parent = $this->get('cms.page.loader')->getParent($page);
			$data['parent'] = isset($data['parent']) ? $data['parent'] : 0;
			// If the parentID != the submitted parent OR the parent is false
			// (as it's root) and the submitted parent is not 0 (root)

			try {
				if (($parent && $parent->id != $data['parent']) || (!$parent && $data['parent'] != 0)) {
					$this->get('cms.page.edit')->changeParent($pageID, $data['parent']);
					$this->addFlash('success', $this->trans('ms.cms.feedback.edit.attributes.parent.success'));
				}

				if (!is_null($data['siblings']) && $data['siblings'] >= 0) {
					$index = $data['siblings'];
					$this->get('cms.page.edit')->changeOrder($page, $index);
				}
			} catch (NestedSetException $e) {
				$this->addFlash('error', $this->trans('ms.cms.feedback.edit.attributes.nested-set.error', ['%error%' => $e->getMessage()]));

				return $this->redirectToReferer();
			} catch (PageEditException $e) {
				$this->addFlash('error', $this->trans('ms.cms.feedback.edit.attributes.order.failure'));
			}
			if (!$page->isHomepage()) {
				$page = $this->_updateSlug($page, $data['slug']);
			}

			$userGroups = $this->get('user.groups');

			$page->visibilitySearch     = $data['visibility_search'];
			$page->visibilityMenu       = $data['visibility_menu'];
			$page->visibilityAggregator = $data['visibility_aggregator'];
			$page->access               = $data['access'] ?: 0;
			
			$page->accessGroups = array_map(function($group) use ($userGroups) {
				return $userGroups->get($group);
			}, $data['access_groups']);

			$page->setTags($this->_parseTags($data['tags']));

			$page = $this->get('cms.page.edit')->save($page);
			$this->addFlash('success', $this->trans('ms.cms.feedback.edit.attributes.success'));
		}

		return $this->redirectToReferer();
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
			throw $this->createNotFoundException(
				$this->trans('ms.cms.feedback.general.failure.non-existing-page', array('%pageID%' => $page->id)),
				null,
				404
			);
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
			if ($data['metaImage']) {
				$page->setMetaImage($this->get('file_manager.file.loader')->getById($data['metaImage']));
			}
			$page = $this->get('cms.page.edit')->save($page);

			$this->addFlash('success', $this->trans('ms.cms.feedback.edit.metadata.success'));
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

		$options = [
			'action' => $this->generateUrl('ms.cp.cms.edit.content.action', [
				'pageID' => $page->id
			]),
		];

		return $this->get('field.form')->generate($content, $options);
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

	protected function _getAttributeForm(Page $page)
	{
		$accessGroups = array();

		foreach ($page->accessGroups as $group) {
			$accessGroups[] = $group->getName();
		}

		$parent = $this->get('cms.page.loader')->getParent($page);
		$form = $this->get('form')
			->setName('attributes')
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.cms.edit.attributes.action', array(
				'pageID' => $page->id,
			)))
			->setDefaultValues(array(
				'slug'                  => $page->slug,
				'visibility_menu'       => $page->visibilityMenu,
				'visibility_search'     => $page->visibilitySearch,
				'visibility_aggregator' => $page->visibilityAggregator,
				'access'                => ($page->accessInherited) ? Authorisation::ACCESS_INHERITED : $page->access,
				'access_groups'         => $accessGroups,
				'tags'                  => implode(', ', $page->getTags()),
				'parent'                => $parent ? $parent->id : 0,
				'siblings'              => '',
			));

		if ($page->slug != '/') {
			$form->add('slug', 'ms_slug', $this->trans('ms.cms.attributes.slug.label'), array(
				'attr' => array('data-help-key' => 'ms.cms.attributes.slug.help'),
			));
		}
		else {
			$form->add('slug', 'text', $this->trans('ms.cms.attributes.slug.label'), array(
				'read_only' => true,
				'data' => $this->trans('ms.cms.attributes.slug.homepage'),
			));
		}

		$form->add('visibility_menu', 'checkbox', $this->trans('ms.cms.attributes.visibility.menu.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.attributes.visibility.menu.help'),
		))->val()->optional();
		$form->add('visibility_search', 'checkbox', $this->trans('ms.cms.attributes.visibility.search.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.attributes.visibility.search.help'),
		))->val()->optional();
		$form->add('visibility_aggregator', 'checkbox', $this->trans('ms.cms.attributes.visibility.aggregator.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.attributes.visibility.aggregator.help'),
		))->val()->optional();

		$accessChoices = array(
			Authorisation::ACCESS_ALL        => $this->trans('ms.cms.attributes.access.options.all'),
			Authorisation::ACCESS_GUEST      => $this->trans('ms.cms.attributes.access.options.guest'),
			Authorisation::ACCESS_USER       => $this->trans('ms.cms.attributes.access.options.user'),
			Authorisation::ACCESS_USER_GROUP => $this->trans('ms.cms.attributes.access.options.group'),
		);

		if ($page->depth > 0) {
			$accessChoices = array(
				Authorisation::ACCESS_INHERITED => $this->trans('ms.cms.attributes.access.options.inherited')
			) + $accessChoices;
		}

		$form->add('access', 'choice', $this->trans('ms.cms.attributes.access.label'), array(
			'choices' => $accessChoices,
			'empty_value' => false,
			'attr' => array('data-help-key' => 'ms.cms.attributes.access.help'),
		));

		$form->add('access_groups', 'choice', $this->trans('ms.cms.attributes.access_groups.label'), array(
			'attr'     => array('data-help-key' => 'ms.cms.attributes.access_groups.help'),
			'choices'  => $this->get('user.groups')->flatten(),
			'multiple' => true,
		))->val()->optional();

		$siblings = $this->get('cms.page.loader')->getSiblings($page);

		$siblingChoices = array();
		if ($siblings) {
			$siblingChoices[0] = 'Move to top';
			foreach ($siblings as $k => $s) {
				// We need to add one to allow for 0 to be the move to top option
				$siblingChoices[$k+1] = $s->title;
			}
		}
		$form->add('siblings', 'choice', $this->trans('ms.cms.attributes.siblings.label'), array(
			'attr'    => array('data-help-key' => 'ms.cms.attributes.siblings.help'),
			'choices' => $siblingChoices,
			'empty_value' => $this->trans('ms.cms.attributes.siblings.placeholder'),
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

		$form->add('tags', 'textarea', $this->trans('ms.cms.attributes.tags.label'), [
			'attr' => [
				'data-help-key' => 'ms.cms.attributes.tags.help'
			],
		])->val()->optional();

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
				'metaImage'       => $page->getMetaImage() ? $page->getMetaImage()->id : null,
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

		$form->add('metaImage', 'file', $this->trans('ms.cms.metadata.image.label'), [
			'attr' => ['data-help-key' => 'ms.cms.metadata.image.help'],
		])->val()
			->optional();

		$files   = (array) $this->get('file_manager.file.loader')->getAll();

		// Get the available files
		$choices = array();
		$allowedTypes = array(File\Type::IMAGE);
		foreach ($files as $file) {
			if ($allowedTypes) {
				if (!in_array($file->typeID, $allowedTypes)) {
					continue;
				}
			}

			$choices[$file->id] = $file->name;
		}

		uasort($choices, function($a, $b) {
			return strcmp($a, $b);
		});

		$form->add('metaImage', 'ms_file', $this->trans('ms.commerce.product.image.file.label'), [
			'choices' => $choices,
			'empty_value' => 'Please select…',
			'attr' => array('data-help-key' => 'ms.commerce.product.image.file.help'),
		]);

		return $form;
	}

	/**
	 * Check to see whether we can update a slug or not. This also checks the
	 * slug history and other pages and sets up the feedback to allow actions
	 * to replace the old slug.
	 *
	 * @param  Page   	$page
	 * @param  Slug 	$slug [description]
	 *
	 * @return Page 	$page
	 */
	protected function _updateSlug(Page $page, Slug $slug)
	{
		try {
			$this->get('cms.page.slug_edit')->updateSlug($page, $slug);
		} catch (SlugUpdateException $e) {
			$this->addFlash('error', $e->getTranslation(), $e->getParams());
		}

		return $page;
	}

	protected function _parseTags($tags)
	{
		if (!$tags) {
			return [];
		}
		if (!is_string($tags) && !is_array($tags)) {
			throw new \InvalidArgumentException('$tags must be a string or an array, ' . gettype($tags) . ' given');
		}
		elseif (is_string($tags)) {
			$tags = explode(',', $tags);
		}

		foreach ($tags as $key => $tag) {
			$tags[$key] = trim($tag);

			if (empty($tags[$key])) {
				unset($tags[$key]);
			}
		}

		return array_unique($tags);
	}
}