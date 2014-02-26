<?php

namespace Message\Mothership\CMS\Exception;

/**
 * Exception for when a page is trying to use a slug that exists on a deleted
 * page.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class DeletedSlugExistsException extends SlugException
{

}