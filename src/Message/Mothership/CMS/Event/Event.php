<?php

namespace Message\Mothership\CMS\Event;

use Message\Cog\Event\Event as BaseEvent;

class Event extends BaseEvent
{
	const PAGE_CREATE  = 'cms.page.create';
	const PAGE_EDIT    = 'cms.page.edit';
	const PAGE_DELETE  = 'cms.page.delete';
	const PAGE_RESTORE = 'cms.page.restore';
}