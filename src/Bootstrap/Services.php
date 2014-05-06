<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Mothership\CMS;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$services['cms.page.types'] = function($c) {
			return new CMS\PageType\Collection;
		};

		$services['cms.page.slug_generator'] = $services->factory(function($c) {
			return new CMS\Page\SlugGenerator($c['cms.page.loader'], (array) $c['cfg']->cms->slug->substitutions);
		});

		$services['cms.page.nested_set_helper'] = $services->factory(function($c) {
			$helper = $c['db.nested_set_helper'];

			return $helper->setTable('page', 'page_id', 'position_left', 'position_right', 'position_depth');
		});

		$services['cms.page.loader'] = $services->factory(function($c) {
			return new CMS\Page\Loader(
				'Locale class',
				$c['db.query'],
				$c['cms.page.types'],
				$c['user.groups'],
				$c['cms.page.authorisation'],
				$c['user.current'],
				$c['cms.page.searcher']
			);
		});

		$services['cms.page.searcher'] = function($c) {
			$searcher =  new CMS\Page\Searcher($c['db.query'], $c['markdown.parser']);

			// Ignore terms less than this length.
			$searcher->setMinTermLength($c['cfg']->search->minTermLength);

			// Fields to in which to search for the terms.
			$searcher->setSearchFields($c['cfg']->search->searchFields);

			// Modifier for result score for fields.
			// Reformat the array due to issues with yaml formatting array keys.
			$tmp = $c['cfg']->search->fieldModifiers;
			$fieldModifiers = array();
			foreach ($tmp as $v) {
				$fieldModifiers[$v[0]] = $v[1];
			}
			$searcher->setFieldModifiers($fieldModifiers);

			// Modifier for the type of page.
			// Reformat the array due to issues with yaml formatting array keys.
			$tmp = $c['cfg']->search->pageTypeModifiers;
			$pageTypeModifiers = array();
			foreach ($tmp as $v) {
				$pageTypeModifiers[$v[0]] = $v[1];
			}
			$searcher->setPageTypeModifiers($pageTypeModifiers);

			$searcher->setExcerptField($c['cfg']->search->excerptField);

			return $searcher;
		};

		$services['cms.page.content_loader'] = $services->factory(function($c) {
			return new CMS\Page\ContentLoader($c['db.query'], $c['field.factory']);
		});

		$services['cms.page.content_edit'] = $services->factory(function($c) {
			return new CMS\Page\ContentEdit($c['db.transaction'], $c['event.dispatcher'], $c['user.current']);
		});

		$services['cms.page.authorisation'] = $services->factory(function($c) {
			return new CMS\Page\Authorisation($c['user.group.loader'], $c['user.current']);
		});

		$services['cms.page.create'] = $services->factory(function($c) {
			return new CMS\Page\Create(
				$c['cms.page.loader'],
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.nested_set_helper'],
				$c['cms.page.slug_generator'],
				$c['user.current']
			);
		});

		$services['cms.page.delete'] = $services->factory(function($c) {
			return new CMS\Page\Delete(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.loader'],
				$c['user.current']
			);
		});

		$services['cms.page.edit'] = $services->factory(function($c) {
			return new CMS\Page\Edit(
				$c['cms.page.loader'],
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.nested_set_helper'],
				$c['user.current']
			);
		});

		$services['cms.search.loader'] = $services->factory(function($c) {
			return new CMS\SearchLog\Loader(
				$c['db.query']
			);
		});

		$services['cms.search.create'] = $services->factory(function($c) {
			return new CMS\SearchLog\Create(
				$c['cms.search.loader'],
				$c['db.query'],
				$c['user.current']
			);
		});

		//// For backwards compatibility with CMS
		$service['cms.field.factory'] = function($c) {
			return $c['field.factory'];
		};

		//// For backwards compatibility with CMS
		$services['cms.field.form'] = function($c) {
			return $c['field.form'];
		};

		$services->extend('field.collection', function($fields, $c) {
			$fields->add(new \Message\Mothership\CMS\FieldType\Link($c['validator']));

			return $fields;
		});

		$services->extend('form.extensions', function($extensions, $c) {
			$extensions[] = $c['form.cms_extension'];

			return $extensions;
		});

		$services['form.cms_extension'] = $services->factory(function($c) {
			$ext = new \Message\Mothership\CMS\FormType\CmsExtension;
			$ext->setContainer($c);

			return $ext;
		});

		$services->extend('form.templates.twig', function($templates, $c) {
			$templates[] = 'Message:Mothership:CMS::form:twig:form_div_layout';

			return $templates;
		});

		$services->extend('form.templates.php', function($templates, $c) {
			$templates[] = 'Message:Mothership:CMS::form:php';

			return $templates;
		});

		$services->extend('user.groups', function($groups) {
			$groups->add(new CMS\UserGroup\ContentManager);

			return $groups;
		});
	}
}