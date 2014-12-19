<?php

namespace Message\Mothership\CMS\Blog;

/**
 * Class FrontEndCommentException
 * @package Message\Mothership\CMS\Blog
 *
 * Exception for handing additional validation on comment creation to display to the user
 *
 * Pass in a translation key for the message
 */
class FrontEndCommentException extends \LogicException
{
}