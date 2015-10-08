<?php

namespace Message\Mothership\CMS\Page\Exception;

use Message\Cog\Exception\TranslationLogicException;

/**
 * Class SlugUpdateException
 * @package Message\Mothership\CMS\Page\Exception
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 *
 * Exception to be thrown when a slug cannot be updated. Extends TranslationLogicException so
 * error messages can be relayed to the user via a flash message.
 */
class SlugUpdateException extends TranslationLogicException
{

}