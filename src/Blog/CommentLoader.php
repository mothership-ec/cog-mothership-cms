<?php

namespace Message\Mothership\CMS\Blog;

use Message\Cog\DB\QueryBuilderFactory;

/**
 * Class CommentLoader
 * @package Message\Mothership\CMS\Blog
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
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
		'website',
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

	/**
	 * Get comments for a specific page
	 *
	 * @param $pageID
	 * @param array $statuses
	 * @throws \InvalidArgumentException
	 *
	 * @return CommentCollection
	 */
	public function getByPage($pageID, array $statuses = null)
	{
		$statuses = $this->_parseStatuses($statuses);

		if (!is_numeric($pageID)) {
			throw new \InvalidArgumentException('Page ID must be a numeric, ' . gettype($pageID) . ' given');
		}

		$pageID = (int) $pageID;

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

	public function getByStatus($statuses, $order = 'ASC')
	{
		$statuses = $this->_parseStatuses((array) $statuses);

		if (!in_array($order, ['ASC', 'DESC'])) {
			throw new \InvalidArgumentException('Order must be either `ASC` or `DESC`');
		}

		$comments = (array) $this->_getSelect()
			->where('status IN (?js)', [$statuses])
			->orderBy('created_at ' . $order)
			->getQuery()
			->run()
			->bindTo('\\Message\\Mothership\\CMS\\Blog\\Comment')
		;

		return new CommentCollection($comments);
	}

	/**
	 * Get QueryBuilder instance with appropriate selected fields
	 *
	 * @return \Message\Cog\DB\QueryBuilder
	 */
	private function _getSelect()
	{
		return $this->_queryBuilderFactory->getQueryBuilder()
			->select($this->_selectFields)
			->from(self::TABLE_NAME)
		;
	}

	/**
	 * Check that requested statuses exist. If no statuses are set, default to all statuses
	 *
	 * @param array $statuses
	 * @throws \LogicException
	 * @throws \InvalidArgumentException
	 *
	 * @return array
	 */
	private function _parseStatuses(array $statuses = null)
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