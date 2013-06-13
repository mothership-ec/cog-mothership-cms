<?php

namespace Message\Mothership\CMS\Controller;

use Message\Mothership\CMS\Page\Create AS CreatePage;

class Create extends \Message\Cog\Controller\Controller
{
	public function index()
	{
		// $loader = $this->_services['cms.page.loader'];
		// $values = $this->buildTree( $loader->getAll() );
		//var_dump($this->_services['cms.page.types']); exit;
		$pageTypes = array(
			'blog',
			'product',
			'listing'
		);

		return $this->render('Message:Mothership:CMS::create', array(
			'pageTypes' => $pageTypes
		));
	}

	public function process()
	{
		if ($data = $this->get('request')->request->get('create')) {
			var_dump($this->_services); exit;
		}
	}
}