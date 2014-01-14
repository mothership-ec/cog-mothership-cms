<?php

namespace Message\Mothership\CMS;

/**
 * Event names for events fired in the Mothership CMS module.
 *
 * Note there are many Page-related events that are not defined here, but in
 * Page\Event\Event. These need moving across to this class at some point.
 *
 * @todo Move Page event identifiers over to this class
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
final class Events
{
	/**
	 * The FRONTEND_BUILD_MENU event occurs when any menu is built on the
	 * frontend of the website.
	 *
	 * This is helpful for filtering menus based on permissions; adding
	 * additional links to the menu and so on.
	 *
	 * @var string
	 */
	const FRONTEND_BUILD_MENU = 'cms.frontend.menu.build';
}