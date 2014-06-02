<?php

namespace Message\Mothership\CMS\Controller\Module\Dashboard;

use Message\Cog\Controller\Controller;

/**
 * CMS summary dashboard module.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class CMSSummary extends Controller
{
	const UPDATED_COUNT = 4;
	const DELETED_COUNT = 3;

	/**
	 * Get recently updated pages.
	 *
	 * @todo Add recently deleted
	 *
	 * @return Message\Cog\HTTP\Response
	 */
	public function index()
	{
		$count = self::UPDATED_COUNT;
		$updated = $this->get('db.query')->run("
			SELECT page_id, title, updated_at
			FROM page
			ORDER BY updated_at DESC
			LIMIT ?i
		", [$count]);

		$count = self::DELETED_COUNT;
		$deleted = $this->get('db.query')->run("
			SELECT page_id, title, deleted_at
			FROM page
			WHERE deleted_at > 0
			ORDER BY deleted_at DESC
			LIMIT ?i
		", [$count]);

		$data = [
			'updated' => $updated->transpose('page_id'),
			'deleted' => $deleted->transpose('page_id')
		];

		return $this->render('Message:Mothership:CMS::modules:dashboard:cms-summary', $data);
	}
}