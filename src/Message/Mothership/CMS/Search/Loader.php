<?php

namespace Message\Mothership\CMS\Search;

use Message\Cog\DB\Query as DBQuery;
use Message\Cog\ValueObject\DateTimeImmutable;

class Loader {

	protected $_query;

	public function __construct(DBQuery $query)
	{
		$this->_query = $query;
	}

	public function getByID($ids)
	{
		return $this->_load($ids, false);
	}

	protected function _load($ids, $alwaysReturnArray = false)
	{
		if (! is_array($ids)) {
			$ids = (array) $ids;
		}

		if (! $ids) {
			return $alwaysReturnArray ? array() : false;
		}

		$result = $this->_query->run('
			SELECT
				*
			FROM
				search_log
			WHERE
				log_id IN (?ij)
		', array($ids));

		if (0 === count($result)) {
			return $alwaysReturnArray ? array() : false;
		}

		$entities = $result->bindTo('Message\\Mothership\\CMS\\Search\\SearchLog');
		$return = array();

		foreach ($entities as $key => $entity) {

			$entity->id = $result[$key]->log_id;

			// Add created authorship
			$entity->authorship->create(
				new DateTimeImmutable(date('c', $result[$key]->created_at)),
				$result[$key]->created_by
			);

			$return[$entity->id] = $entity;
		}

		return $alwaysReturnArray || count($return) > 1 ? $return : reset($return);
	}

}