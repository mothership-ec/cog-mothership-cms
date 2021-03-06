<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Mothership\CMS\Page\Page;
use Message\Cog\Filesystem\File;

class Social extends Controller
{
	public function share(Page $page, $description = null, File $image = null, array $networks = null)
	{
		if ($networks === null) {
			$networks = ['facebook', 'twitter'];
		}

		$schemeAndHost = rtrim($this->get('http.request.master')->getSchemeAndHttpHost(), '/');
		// empty breaks so homepage in this case
		$slug = empty($page->slug) ? '/' : $page->slug;
		$uri  = $schemeAndHost . '/' . trim($this->generateUrl('ms.cms.frontend', ['slug' => $slug]), '/');
	
		return $this->render('Message:Mothership:CMS::modules:social:share', [
			'social'      => $this->get('cfg')->social,
			'uri'         => $uri,
			'title'       => $page->metaTitle ?: $page->title,
			'description' => $description ?: $page->metaDescription,
			'imageUri'    => $image ? $schemeAndHost . $image->getUrl() : null,
			'networks'    => $networks,
		]);
	}

	public function links(array $networks = null)
	{
		$cfg = $this->get('cfg')->social;

		// if null then use all in the config
		if ($networks === null) {
			$networks = [];
			foreach ($cfg as $network => $values) {
				$networks[] = $network;
			}
		}

		return $this->render('Message:Mothership:CMS::modules:social:links', [
			'networks' => $networks,
			'social' => $this->get('cfg')->social,
		]);
	}
}