<?php

namespace Message\Mothership\CMS\Asset;

use Message\Mothership\CMS\Asset\Asset;
use Message\Cog\DB\Query;

class Delete {

	protected $_asset;

	public function __construct(Asset $asset, Query $query)
	{
		$this->_asset = $asset;
		$this->_query = $query;

	}

	public function delete()
	{

	}
}