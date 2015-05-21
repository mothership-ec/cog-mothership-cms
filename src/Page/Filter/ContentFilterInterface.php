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
	/**
	 * Set the content field to filter by
	 *
	 * @param string $name              Name of the field to filter by
	 * @param string | null $group      Name of group field belongs to, if necessary
	 */
	public function setField($name, $group = null);
}