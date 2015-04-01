<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\DB\Entity\EntityLoaderInterface;
use Message\Mothership\FileManager\File\Loader as FileLoader;

class ImageLoader extends FileLoader implements EntityLoaderInterface
{
	public function load(Page $page)
	{
		$result = $this->_query->run('
				SELECT `meta_image` FROM `page`
				WHERE `page_id` = ?i AND `meta_image` IS NOT NULL;
			', $page->id);

		if (count($result)){
			$pages = $this->getByID($result->flatten());
			return $pages ? array_shift($pages) : null;
		} else {
			return null;	
		}
	}
}