<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

use Message\Mothership\CMS\Field;

use Message\Mothership\CMS\Page\Authorisation;
use Message\Mothership\CMS\Page\Page;
use Message\Mothership\CMS\Page\Content;

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
	public function content($pageID)
	{
		$page    = $this->get('cms.page.loader')->getByID($pageID);
		$content = $this->get('cms.page.content_loader')->load($page);
		$form    = $this->_getContentForm($page, $content);

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
			'form'        => $form,
			'content'     => $content,
			'repeatables' => $repeatables,
		));
	}

	public function contentAction($pageID)
	{
		$page    = $this->get('cms.page.loader')->getByID($pageID);
		$content = $this->get('cms.page.content_loader')->load($page);
		$form    = $this->_getContentForm($page, $content);

		// Redirect user back to the form if there are any errors
		if (!$form->isValid()) {
			return $this->redirectToReferer();
		}

		$content = $this->get('cms.page.content_edit')->updateContent($form->getFilteredData(), $content);

		if ($this->get('cms.page.content_edit')->save($page, $content)) {
			$this->addFlash('success', 'Content updated successfully');
		}
		else {
			$this->addFlash('error', 'An error occured while updating content');
		}

		return $this->redirectToReferer();
	}

	/**
	 * Render the attributes form.
	 *
	 * @param int $pageID The page ID
	 */
	public function attributes($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);
		$parent = $this->get('cms.page.loader')->getParentID($page);
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
				'parent'				=> $parent ? $parent->id : 0,
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

		$parents = $this->get('cms.page.loader')->getAllParents();
		$choices = array();
		foreach ($parents as $p) {
			$choices[$p->id] = $p->title;
		}

		$form->add('parent', 'choice', 'Parent', array('choices' => $choices));
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
	 * Render metadata form
	 *
	 * @param int $pageID
	 *
	 * @return Response
	 */
	public function metadata($pageID)
	{
		$page = $this->get('cms.page.loader')->getByID($pageID);

		$form = $this->_getMetadataForm($page);

		return $this->render('::edit/metadata', array(
			'page' => $page,
			'form' => $form,
		));
	}

	/**
	 * Change the order of the children within a nested set. This would also move
	 * the children nodes of any entry that is affected by the move.
	 *
	 * @param  int 		$pageID 		The id of the page we are going to move
	 * @param  int  	$newIndexInSet	The index of the position itn the subtree
	 */
	public function changeOrder()
	{
		$ns = $this->get('cms.page.nested_set_helper');
		$trans = $ns->move(46,9);
		var_dump($trans->commit()); exit;
	}

	/**
	 * This will move a node to a different parent of the tree.
	 *
	 * @param int 	$pageID 		The ID of the page we are going to move
	 * @param int   $newParentID 	The ID of the new parent we are moving to
	 */
	public function changeParent($pageID, $newParentID)
	{

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
		$page   = $this->get('cms.page.loader')->getByID($pageID);
		$form   = $this->_getMetadataForm($page);

		if ($form->isValid() && ($data = $form->getFilteredData())) {

			foreach ($data as $key => $value) {
				$page->$key = (!empty($value)) ? $value : $page->$key;
			}

			$this->get('cms.page.edit')->save($page);

			$this->addFlash('success', 'Metadata successfully saved');
		}

		return $this->redirectToReferer();

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
			->setMethod('POST')
			->setAction($this->generateUrl('ms.cp.cms.edit.content.action', array(
				'pageID' => $page->id,
			)));

		return $this->get('cms.field.form')->generate($form, $content);
	}

	/**
	 * Get form for metadata section of edit page
	 *
	 * @param Page $page
	 *
	 * @return \Message\Cog\Form\Handler
	 */
	protected function _getMetadataForm(Page $page, Content $content = null)
	{
		$defaults = array(
			'metaTitle' => $page->metaTitle,
			'metaDescription' => $page->metaDescription,
			'metaHtmlHead' => $page->metaHtmlHead,
			'metaHtmlFoot' => $page->metaHtmlFoot,
		);

		if (!$content) {
			$content = $this->get('cms.page.content_loader')->load($page);
		}

		$form = $this->get('form');
		$form->setAction($this->generateUrl('ms.cp.cms.edit.metadata.action', array(
			'pageID' => $page->id
		)))
			->setMethod('post')
			->setDefaultValues($defaults);
		$form->add('metaTitle', 'text', $this->trans('ms.cms.metadata.title.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.metadata.title.help')
		))
			->val()
			->optional()
			->maxLength(255);
		$form->add('metaDescription', 'textarea', $this->trans('ms.cms.metadata.description.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.metadata.description.help')
		))
			->val()
			->optional();
		$form->add('metaHtmlHead', 'textarea', $this->trans('ms.cms.metadata.htmlHead.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.metadata.htmlHead.help')
		))
			->val()
			->optional();
		$form->add('metaHtmlFoot', 'textarea', $this->trans('ms.cms.metadata.htmlFoot.label'), array(
			'attr' => array('data-help-key' => 'ms.cms.metadata.htmlFoot.help')
		))
			->val()
			->optional();

		return $form;


	}
}