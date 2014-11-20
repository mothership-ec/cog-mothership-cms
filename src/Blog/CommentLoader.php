<?php

namespace Message\Mothership\CMS\Blog;

use Message\Cog\DB\QueryBuilderFactory;

class CommentLoader
{
	const TABLE_NAME = 'blog_comment';

	/**
	 * @var \Message\Cog\DB\QueryBuilderFactory
	 */
	private $_queryBuilderFactory;

	/**
	 * @var Statuses
	 */
	private $_statuses;

	/**
	 * @var array
	 */
	private $_selectFields = [
		'comment_id AS id',
		'page_id AS pageID',
		'user_id AS userID',
		'name',
		'email_address AS email',
		'content',
		'ip_address AS ipAddress',
		'created_at AS createdAt',
		'updated_at AS updatedAt',
		'updated_by AS updatedBy',
		'status',
	];

	public function __construct(QueryBuilderFactory $queryBuilderFactory, Statuses $statuses)
	{
		$this->_queryBuilderFactory = $queryBuilderFactory;
		$this->_statuses            = $statuses;
	}

	public function getByPage($pageID, array $statuses = null)
	{
		$statuses = $this->_parseTypes($statuses);

		if (!is_int($pageID)) {
			throw new \InvalidArgumentException('Page ID must be an integer, ' . gettype($pageID) . ' given');
		}

		$comments = (array) $this->_getSelect()
			->where('page_id = :pageID?i', ['pageID' => $pageID])
			->where('status IN (?js)', [$statuses])
			->orderBy('created_at ASC')
			->getQuery()
			->run()
			->bindTo('\\Message\\Mothership\\CMS\\Blog\\Comment')
		;

		return new CommentCollection($comments);
	}

	private function _getSelect()
	{
		return $this->_queryBuilderFactory->getQueryBuilder()
			->select($this->_selectFields)
			->from(self::TABLE_NAME)
		;
	}

	private function _parseTypes(array $statuses)
	{
		if (null === $statuses) {
			return array_keys($this->_statuses->getStatuses());
		}

		foreach ($statuses as $status) {
			if (!is_string($status)) {
				throw new \InvalidArgumentException('Status type in array must be a string, ' . gettype($status) . ' given');
			}
			if (!array_key_exists($status, $this->_statuses->getStatuses())) {
				throw new \LogicException('Status `' . $status . '` is not valid');
			}
		}

		return $statuses;
	}
}