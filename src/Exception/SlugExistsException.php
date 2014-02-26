<?php

namespace Message\Mothership\CMS\Exception;

use Message\Mothership\CMS\Page\Page;

use Message\Cog\ValueObject\Slug;

/**
 * Exception for when a page is trying to use a slug that exists on a published
 * page.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class SlugExistsException extends SlugException
{

}