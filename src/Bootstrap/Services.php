<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Mothership\CMS;

use Message\Cog\DB\Entity\EntityLoaderCollection;
use Message\Cog\Bootstrap\ServicesInterface;
use Message\Mothership\Report\Report\Collection as ReportCollection;

class Services implements ServicesInterface
{
	public function registerServices($services)
	{
		$this->registerReports($services);

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
				$c['db.query.builder.factory'],
				$c['cms.page.types'],
				$c['user.groups'],
				$c['cms.page.authorisation'],
				$c['user.current'],
				$c['cms.page.searcher'],
				new EntityLoaderCollection([
					'content' => $c['cms.page.content_loader'],
					'image' => $c['cms.page.image.loader'],
					'tags' => $c['cms.page.tag.loader'],
				]),
				$c['cms.page.cache']
			);
		});

		$services['cms.page.cache'] = function($c) {
			return new CMS\Page\PageCollection;
		};

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
			return new CMS\Page\ContentLoader($c['db.query'], $c['field.factory'], $c['field.content.builder']);
		});

		$services['cms.page.image.loader'] = $services->factory(function($c) {
			return new CMS\Page\ImageLoader($c['locale'], $c['db.query']);
		});

		$services['cms.page.content_edit'] = $services->factory(function($c) {
			return new CMS\Page\ContentEdit($c['db.transaction'], $c['event.dispatcher'], $c['user.current']);
		});

		$services['cms.page.authorisation'] = $services->factory(function($c) {
			return new CMS\Page\Authorisation($c['user.group.loader'], $c['user.current']);
		});

		$services['cms.page.tag.loader'] = function($c) {
			return new CMS\Page\TagLoader($c['db.query.builder.factory']);
		};

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
				$c['db.transaction'],
				$c['event.dispatcher'],
				$c['cms.page.nested_set_helper'],
				$c['user.current']
			);
		});

		$services['cms.page.cookietrail.builder'] = $services->factory(function($c) {
			return new CMS\Page\CookieTrail\CookieTrailBuilder($c['cms.page.loader']);
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

		$services['cms.blog.content_validator'] = function($c) {
			return new CMS\Blog\ContentValidator;
		};

		$services['cms.blog.comment_builder'] = $services->factory(function($c) {
			return new CMS\Blog\CommentBuilder($c['user.current'], $c['request'], $c['cms.blog.content_validator'], $c['user.group.loader']);
		});

		$services['cms.blog.comment_loader'] = function($c) {
			return new CMS\Blog\CommentLoader($c['db.query.builder.factory'], $c['cms.blog.comment_statuses']);
		};

		$services['cms.blog.comment_create'] = function($c) {
			return new CMS\Blog\CommentCreate($c['db.query']);
		};

		$services['cms.blog.comment_edit'] = function($c) {
			return new CMS\Blog\CommentEdit($c['db.transaction']);
		};

		$services['cms.blog.comment_permission_resolver'] = function($c) {
			return new CMS\Blog\CommentPermissionResolver($c['cms.blog.content_validator'], $c['user.group.loader']);
		};

		$services['cms.blog.comment_statuses'] = function($c) {
			return new CMS\Blog\Statuses;
		};

		$services['cms.blog.comment_dashboard_loader'] = function($c) {
			return new CMS\Blog\Dashboard\DashboardLoader($c['cms.blog.comment_loader'], $c['cms.page.loader'], $c['cms.page.types']);
		};

		$services->extend('field.collection', function($fields, $c) {
			$fields->add(new \Message\Mothership\CMS\FieldType\Link($c['cms.page.loader']));

			return $fields;
		});

		$services['cms.page.slug_edit'] = function ($c) {
			return new CMS\Page\SlugEdit(
				$c['cms.page.loader'],
				$c['cms.page.edit'],
				$c['routing.matcher'],
				$c['routing.generator']
			);
		};

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

		$services['form.blog_comment'] = $services->factory(function($c) {
			return new \Message\Mothership\CMS\Form\BlogComment($c['user.current']);
		});

		$services['form.manage_comments'] = $services->factory(function($c) {
			return new CMS\Form\ManageComments($c['cms.blog.comment_statuses']);
		});

		$services['form.contact'] = $services->factory(function($c) {
			return new \Message\Mothership\CMS\Form\Contact($c['translator']);
		});

		$services['form.publishschedule'] = $services->factory(function() {
			return new \Message\Mothership\CMS\Form\PublishSchedule();
		});

		$services->extend('user.groups', function($groups) {
			$groups->add(new CMS\UserGroup\ContentManager);

			return $groups;
		});

		$services['mail.factory.contact'] = $services->factory(function($c) {
			$factory = new \Message\Cog\Mail\Factory($c['mail.message']);

			$factory->requires('email', 'name', 'message');
			$toEmail = $c['cfg']->app->defaultContactEmail;
			$appName = $c['cfg']->app->name;

			$factory->extend(function($factory, $message) use ($appName, $toEmail) {
				$message->setFrom($factory->email, $factory->name);
				$message->setTo($toEmail, $appName);
				$message->setSubject('New contact from the ' . $appName . ' website');
				$message->setView('Message:Mothership:CMS::mail:contact', [
					'name'    => $factory->name,
					'email'   => $factory->email,
					'message' => $factory->message,
				]);
			});

			return $factory;
		});
	}

	public function registerReports($services)
	{
		$services['cms.search_terms'] = $services->factory(function($c) {
			return new CMS\Report\SearchTerms(
				$c['db.query.builder.factory'],
				$c['routing.generator']
			);
		});

		$services['cms.reports'] = function($c) {
			$reports = new ReportCollection;
			$reports
				->add($c['cms.search_terms'])
			;

			return $reports;
		};
	}
}
