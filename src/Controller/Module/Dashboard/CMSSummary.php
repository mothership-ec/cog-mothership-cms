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
	const CACHE_KEY = 'dashboard.module.cms-summary';
	const CACHE_TTL = 3600;

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
		// if (false === $data = $this->get('cache')->fetch(self::CACHE_KEY)) {
			$updated = $deleted = [];

			$count = self::UPDATED_COUNT;
			$updated = $this->get('db.query')->run("
				SELECT page_id, title, updated_at
				FROM page
				ORDER BY updated_at DESC
				LIMIT {$count}
			");

			$updated = array_slice($updated->transpose('page_id'), 0, self::UPDATED_COUNT);

			$data = [
				'updated' => $updated
			];

			$this->get('cache')->store(self::CACHE_KEY, $data, self::CACHE_TTL);
		// }

		return $this->render('Message:Mothership:CMS::modules:dashboard:cms-summary', $data);
	}
}