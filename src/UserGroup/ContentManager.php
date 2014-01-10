<?php

namespace Message\Mothership\CMS\UserGroup;

use Message\User\Group;

class ContentManager implements Group\GroupInterface
{
	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return 'ms-content-manager';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDisplayName()
	{
		return 'Mothership Content Managers';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getDescription()
	{
		return 'Content managers have full permissions in the CMS section of Mothership.';
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerPermissions(Group\Permissions $permissions)
	{
		$permissions
			->addRouteCollection('ms.cp.cms');
	}
}