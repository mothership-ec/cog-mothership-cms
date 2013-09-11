<?php

namespace Message\Mothership\CMS\Search;

use InvalidArgumentException;
use Message\User\UserInterface;
use Message\Cog\DB\Query as DBQuery;
use Message\Cog\ValueObject\DateTimeImmutable;

/**
 * Create decorator for search logs.
 *
 * @author Laurence Roberts <laurence@message.co.uk>
 */
class Create {

	protected $_loader;
	protected $_query;
	protected $_user;

	public function __construct(Loader $loader, DBQuery $query, UserInterface $user) {
		$this->_loader = $loader;
		$this->_query  = $query;
		$this->_user   = $user;
	}

	public function create(SearchLog $search)
	{
		$this->_validate($search);

		// Set create authorship data if not already set
		if (! $search->authorship->createdAt()) {
			$search->authorship->create(
				new DateTimeImmutable,
				$this->_user->id
			);
		}

		$result = $this->_query->run('
			INSERT INTO
				search_log
			SET
				term       = :term?s,
				referrer   = :referrer?sn,
				ip_address = :ipAddress?sn,
				created_at = :createdAt?i,
				created_by = :createdBy?in
		', array(
			'term'      => $search->term,
			'referrer'  => $search->referrer,
			'ipAddress' => $search->ipAddress,
			'createdAt' => $search->authorship->createdAt(),
			'createdBy' => $search->authorship->createdBy(),
		));

		return $this->_loader->getById($result->id());
	}

	protected function _validate(SearchLog $search)
	{
		if (! $search->term or empty($search->term)) {
			throw new InvalidArgumentException("Could not create search log: term is not set or invalid");
		}

		if (! $search->ipAddress or empty($search->ipAddress)) {
			throw new InvalidArgumentException("Could not create search log: ipAddress is not set or invalid");
		}
	}

}