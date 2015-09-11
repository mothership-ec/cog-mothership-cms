<?php

namespace Message\Mothership\CMS\Page\Event;

use Message\Mothership\CMS\Page;

class ContentEvent extends Event
{
	const EDIT = 'cms.page.content.edit';
	const CREATE = 'cms.page.content.create';

	private $_content;

	public function __construct(Page\Page $page, Page\Content $content)
	{
		$this->_content = $content;

		parent::__construct($page);
	}

	public function setContent(Page\Content $content)
	{
		$this->_content = $content;
	}

	public function getContent()
	{
		return $this->_content;
	}
}