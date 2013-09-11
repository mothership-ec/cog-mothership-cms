<?php

namespace Message\Mothership\CMS\Search;

use Message\Cog\ValueObject\Authorship;

class SearchLog {

	public $id;
	public $term;
	public $referrer;
	public $ipAddress;
	public $authorship;

	public function __construct()
	{
		$this->authorship = new Authorship;
	}

}