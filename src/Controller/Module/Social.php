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
		$uri           = $schemeAndHost . $this->generateUrl('ms.cms.frontend', array('slug' => ltrim($page->slug, '/')));
	
		return $this->render('Message:Mothership:CMS::modules:social:share', [
			'networks'    => $this->get('cfg')->social,
			'uri'         => $uri,
			'title'       => $page->metaTitle ?: $page->title,
			'description' => $description ?: $page->metaDescription,
			'imageUri'    => $image ? $schemeAndHost . $image->getUrl() : null,
		]);
	}

	public function links()
	{
		return $this->render('Message:Mothership:CMS::modules:social:links', [
			'networks' => $this->get('cfg')->social,
		]);
	}
}