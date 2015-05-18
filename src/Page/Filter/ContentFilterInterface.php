<?php

namespace Message\Mothership\CMS\Page\Filter;

/**
 * Interface ContentFilterInterface
 * @package Message\Mothership\CMS\Page\Filter
 *
 * @author  Thomas Marchant <thomas@mothership.ec>
 */
interface ContentFilterInterface
{
	public function setField($name, $group = null);
}