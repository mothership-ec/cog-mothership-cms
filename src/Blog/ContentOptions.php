<?php

namespace Message\Mothership\CMS\Blog;

/**
 * Class ContentOptions
 * @package Message\Mothership\CMS\Blog
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Class containing constants determining names of comment fields on the blog page type
 */
class ContentOptions
{
	// Display
	const COMMENTS       = 'comments';
	const ALLOW_COMMENTS = 'allow_comments';
	const APPROVE        = 'approve';
	const ALLOW          = 'allow';
	const DISABLED       = 'disabled';

	// Access
	const PERMISSION = 'comment_permission';
	const GUEST      = 'guest';
	const LOGGED_IN  = 'logged_in';
}