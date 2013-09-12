<?php

namespace Message\Mothership\CMS\SearchLog;

use Message\Cog\ValueObject\Authorship;

/**
 * Search log model.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class SearchLog {

	public $id;
	public $term;
	public $referrer;
	public $ipAddress;
	public $authorship;

	/**
	 * Construct with authorship.
	 */
	public function __construct()
	{
		$this->authorship = new Authorship;
		$this->authorship->disableUpdate();
		$this->authorship->disableDelete();
	}

}