<?php

namespace Message\Mothership\CMS\Exception;

/**
 * Exception for when a page is trying to use a slug that exists in the slug
 * history for another page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 */
class HistoricalSlugExistsException extends SlugException
{

}