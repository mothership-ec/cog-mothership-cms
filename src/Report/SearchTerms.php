<?php

namespace Message\Mothership\CMS\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Localisation\Translator;
use Message\Cog\Routing\UrlGenerator;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;

class SearchTerms extends AbstractReport
{
	public function __construct(QueryBuilderFactory $builderFactory, Translator $trans, UrlGenerator $routingGenerator)
	{
		$this->name = 'search_terms';
		$this->displayName = 'Search Terms';
		$this->reportGroup = "Logs";
		$this->_charts = [new TableChart];
		parent::__construct($builderFactory,$trans,$routingGenerator);
	}

	public function getCharts()
	{
		$data = $this->_dataTransform($this->_getQuery()->run());
		$columns = $this->getColumns();

		foreach ($this->_charts as $chart) {
			$chart->setColumns($columns);
			$chart->setData($data);
		}

		return $this->_charts;
	}

	public function getColumns()
	{
		$columns = [
			['type' => 'string', 	'name' => "Search Term",],
			['type' => 'number',	'name' => "Frequency",	],
		];

		return json_encode($columns);
	}

	/**
	 * Gets the query as an string
	 *
	 * @param  string   $date         Unix date from which to get data
	 * @param  bool     $today        If date selected is current day
	 *
	 * @return string
	 */
	private function _getQuery()
	{
		$queryBuilder = $this->_builderFactory->getQueryBuilder();

		//->where('created_at >= ?')
		//->where('created_at <= ?')

		$queryBuilder
			->select('term AS "Term"')
			->select('COUNT(log_id) AS "Frequency"')
			->from('search_log')
			->groupBy('term')
			->orderBY('Frequency DESC')
		;

		return $queryBuilder->getQuery();
	}

	protected function _dataTransform($data)
	{
		$result = [];

		foreach ($data as $row) {
			$result[] = [
				$row->Term,
				$row->Frequency,
			];
		}

		return json_encode($result);
	}
}