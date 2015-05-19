<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Cog\Filter\FilterCollection;

/**
 * Class PageFilter
 * @package Message\Mothership\CMS\Controller\Module
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Module for handling page filtering form
 */
class PageFilter extends Controller
{
	/**
	 * @param string | FilterCollection $filters       The name of the service for the filter collection to use when
	 *                                                 building the form
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function filterForm($filters)
	{
		if (!is_string($filters) && !$filters instanceof FilterCollection) {
			throw new \InvalidArgumentException('Parameter to render filter form must be either the service name of the filter collection or an instance of \\Message\\Cog\\Filter\\FilterCollection');
		} elseif (is_string($filters)) {

			$filterCollection = $this->get($filters);

			if (!$filterCollection instanceof FilterCollection) {
				throw new \LogicException('Service with name `' . $filters . '` must be an instance of \\Message\\Cog\\Filter\\FilterCollection');
			}

			$filters = $filterCollection;
		}

		return $this->render('Message:Mothership:CMS::modules:filter_form', [
			'form' => $this->createForm($this->get('filter.form_factory')->getForm($filters))
		]);
	}
}