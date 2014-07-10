<?php

namespace Message\Mothership\CMS\FormType;

use Message\Cog\Service\ContainerAwareInterface;
use Message\Cog\Service\ContainerInterface;
use Symfony\Component\Form\AbstractExtension;

class CmsExtension extends AbstractExtension implements ContainerAwareInterface
{
	protected $_container;

	public function setContainer(ContainerInterface $container)
	{
		$this->_container = $container;

		return $this;
	}

	protected function loadTypes()
	{
		return [
			new AnyLink,
			new CmsLink,
			new ExternalLink,
			new MothershipFile,
			new Slug
		];
	}
}