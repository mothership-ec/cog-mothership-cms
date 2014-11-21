<?php

namespace Message\Mothership\CMS\Blog;

use Message\Cog\DB\Transaction;
use Message\Cog\DB\TransactionalInterface;

use Message\User\UserInterface;

class CommentEdit implements TransactionalInterface
{
	private $_trans;
	private $_transOverride = false;

	public function __construct(Transaction $trans)
	{
		$this->_trans = $trans;
	}

	public function setTransaction(Transaction $trans)
	{
		$this->_trans = $trans;
		$this->_transOverride = true;
	}

	public function save(Comment $comment, UserInterface $user)
	{
		$this->_save($comment, $user);
		$this->_commitTransaction();
	}

	public function saveBatch(CommentCollection $comments, UserInterface $user)
	{
		foreach ($comments as $comment) {
			$this->_save($comment, $user);
		}

		$this->_commitTransaction();
	}

	private function _save(Comment $comment, UserInterface $user)
	{
		$this->_trans->add("
			UPDATE
				blog_comment
			SET
				page_id       = :pageID?i,
				user_id       = :userID?i,
				`name`        = :name?s,
				email_address = :email?s,
				website       = :website?sn,
				content       = :content?s,
				ip_address    = :ipAddress?s,
				updated_at    = :updatedAt?d,
				updated_by    = :updatedBy?in,
				status        = :status?s
			WHERE
				comment_id    = :id?i
		", [
			'id'        => $comment->getID(),
			'pageID'    => $comment->getPageID(),
			'userID'    => $comment->getUserID(),
			'name'      => $comment->getName(),
			'email'     => $comment->getEmail(),
			'website'   => $comment->getWebsite(),
			'content'   => $comment->getContent(),
			'ipAddress' => $comment->getIpAddress(),
			'updatedAt' => new \DateTime(),
			'updatedBy' => $user->id,
			'status'    => $comment->getStatus(),
		]);
	}

	private function _commitTransaction()
	{
		if (false === $this->_transOverride) {
			$this->_trans->commit();
		}
	}
}