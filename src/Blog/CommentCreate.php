<?php

namespace Message\Mothership\CMS\Blog;

use Message\Cog\DB\Query;

class CommentCreate
{
	private $_query;

	public function __construct(Query $query)
	{
		$this->_query = $query;
	}

	public function save(Comment $comment)
	{
		$result = $this->_query->run("
			INSERT INTO
				blog_comment
			SET
				page_id = :pageID?i,
				user_id = :userID?in,
				`name`  = :name?s,
				email_address = :email?s,
				content = :content?s,
				ip_address = :ipAddress?s,
				created_at = :createdAt?d,
				updated_at = :updatedAt?d,
				updated_by = :updatedBy?i,
				status = :status?s
		", [
			'pageID'    => $comment->getPageID(),
			'userID'    => $comment->getUserID(),
			'name'      => $comment->getName(),
			'email'     => $comment->getEmail(),
			'content'   => $comment->getContent(),
			'ipAddress' => $comment->getIpAddress(),
			'createdAt' => new \DateTime(),
			'updatedAt' => new \DateTime(),
			'updatedBy' => $comment->getUserID(),
			'status'    => $comment->getStatus(),
		]);

		$comment->setID($result->id());

		return $comment;
	}
}