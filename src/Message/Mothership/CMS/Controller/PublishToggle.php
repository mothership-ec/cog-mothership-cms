<?php

namespace Message\Mothership\CMS\Controller;

class PublishToggle extends \Message\Cog\Controller\Controller
{
	public function index()
	{
		return $this->render('Message:Mothership:CMS::publish_toggle', array(
		));
	}
}