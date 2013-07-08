<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Mothership\CMS;

use Message\Cog\Bootstrap\ServicesInterface;

class Services implements ServicesInterface
{
	public function registerServices($serviceContainer)
	{
		$serviceContainer['markdown.parser'] = function() {
			return new \dflydev\markdown\MarkdownParser;
		};

		$serviceContainer['cms.page.types'] = $serviceContainer->share(function($c) {
			return new CMS\PageType\Collection;
		});

		$serviceContainer['cms.page.slug_generator'] = function($c) {
			return new CMS\Page\SlugGenerator($c['cms.page.loader'], (array) $c['cfg']->cms->slug->substitutions);
		};

		$serviceContainer['cms.page.nested_set_helper'] = function($c) {
			$helper = $c['db.nested_set_helper'];

			return $helper->setTable('page', 'page_id', 'position_left', 'position_right', 'position_depth');
		};

		$serviceContainer['cms.page.loader'] = function($c) {
			return new CMS\Page\Loader(
				'Locale class',
				$c['db.query'],
				$c['cms.page.types'],
				$c['user.groups']
			);
		};

		$serviceContainer['cms.page.content_loader'] = function($c) {
			return new CMS\Page\ContentLoader($c['db.query'], $c['cms.field.factory']);
		};

		$serviceContainer['cms.page.content_edit'] = function($c) {
			return new CMS\Page\ContentEdit($c['db.transaction'], $c['event.dispatcher'], $c['user.current']);
		};

		$serviceContainer['cms.page.authorisation'] = function($c) {
			return new CMS\Page\Authorisation($c['user.group.loader'], $c['user.current']);
		};

		$serviceContainer['cms.page.create'] = function($c) {
			return new CMS\Page\Create(
				$c['cms.page.loader'],
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.nested_set_helper'],
				$c['cms.page.slug_generator'],
				$c['user.current']
			);
		};

		$serviceContainer['cms.page.delete'] = function($c) {
			return new CMS\Page\Delete(
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.loader'],
				$c['user.current']
			);
		};

		$serviceContainer['cms.page.edit'] = function($c) {
			return new CMS\Page\Edit(
				$c['cms.page.loader'],
				$c['db.query'],
				$c['event.dispatcher'],
				$c['cms.page.nested_set_helper'],
				$c['user.current']
			);
		};

		$serviceContainer['cms.field.factory'] = function($c) {
			$factory = new CMS\Field\Factory($c['validator'], $c);

			return $factory;
		};

		$serviceContainer['cms.field.form'] = function($c) {
			return new CMS\Field\Form($c);
		};

		$serviceContainer['form.factory'] = $serviceContainer->share(
			$serviceContainer->extend('form.factory', function($factory, $c) {
				$factory->addExtensions(array(
					$c['form.cms_extension']
				));

				return $factory;
			})
		);

		$serviceContainer['form.cms_extension'] = function($c) {
			$ext = new \Message\Mothership\CMS\Field\FormType\CmsExtension;
			$ext->setContainer($c);

			return $ext;
		};

		$serviceContainer['templating.filesystem.loader'] = $serviceContainer->share(
			$serviceContainer->extend('templating.filesystem.loader', function($loader, $c) {
				$loader->addTemplatePathPatterns(array(
					'cog://Message:Mothership:CMS::View:Form:Php',
					'cog://Message:Mothership:CMS::View:Form:Twig',
				));

				return $loader;
			})
		);

		$serviceContainer['form.templates.twig'] = $serviceContainer->extend(
			'form.templates.twig', function($templates, $c) {
			$templates[] = 'Message:Mothership:CMS::Form:Twig:form_div_layout';

			return $templates;
		});

		$serviceContainer['form.templates.php'] = $serviceContainer->extend(
			'form.templates.php', function($templates, $c) {
				$templates[] = 'Message:Mothership:CMS::Form:Php';

				return $templates;
			}
		);
	}
}