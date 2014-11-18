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

	public function __construct(QueryBuilderFactory $queryBuilderFactory)
	{
		$this->_queryBuilderFactory = $queryBuilderFactory;
	}

	public function getByPage($pageID)
	{
		if (!is_int($pageID)) {
			throw new \InvalidArgumentException('Page ID must be an integer, ' . gettype($pageID) . ' given');
		}

		$comments = (array) $this->_getSelect()
			->where('page_id = :pageID?i', ['pageID' => $pageID])
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
}