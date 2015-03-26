<?php

namespace Message\Mothership\CMS\Page;

/**
 * Holds ordering constants
 *
 * @author Sam Trangmar-Keates <sam@message.co.uk>
 *
 */
class PageOrder
{
	const CREATED_DATE         = "order.date.created.asc";
	const CREATED_DATE_REVERSE = "order.date.created.desc";
	const UPDATED_DATE         = "order.date.updated.asc";
	const UPDATED_DATE_REVERSE = "order.date.updated.desc";
	const ID                   = "order.id.asc";
	const ID_REVERSE           = "order.id.desc";
	const DEFAULT              = "order.natural.asc";
	const REVERSE              = "order.natural.desc";
}