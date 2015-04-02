<?php

namespace Message\Mothership\CMS\Controller\Module;

use Message\Cog\Controller\Controller;
use Message\Mothership\CMS\Page\Page;
use Message\Cog\Filesystem\File;

class Social extends Controller
{
	public function share(Page $page, $description = null, File $image = null)
	{
		$schemeAndHost = $this->get('http.request.master')->getSchemeAndHttpHost();
		$trimmed       = rtrim($page->slug, '/');
		$uri           = $schemeAndHost . $this->generateUrl('ms.cms.frontend', ['slug' => !empty($trimmed) ? $trimmed : $page->slug]);
	
		return $this->render('Message:Mothership:CMS::modules:social:share', [
			'social'      => $this->get('cfg')->social,
			'uri'         => $uri,
			'title'       => $page->metaTitle ?: $page->title,
			'description' => $description ?: $page->metaDescription,
			'imageUri'    => $image ? $schemeAndHost . $image->getUrl() : null,
		]);
	}

	public function links()
	{
		return $this->render('Message:Mothership:CMS::modules:social:links', [
			'social' => $this->get('cfg')->social,
		]);
	}
}