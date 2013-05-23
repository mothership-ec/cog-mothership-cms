<?php

namespace Message\Mothership\CMS\Comment;

class Comment
{
	public $authorship;

	public $id;
	public $user;
	public $name;
	public $email;
	public $ipAddress;
	public $website;
	public $body;

	public $reportedAt;
	public $reportedBy;

	public $approvedAt;
	public $approvedBy;
}