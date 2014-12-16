<?php

namespace Message\Mothership\CMS\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Cog\Routing\UrlGenerator;

use Message\Mothership\Report\Report\AbstractReport;
use Message\Mothership\Report\Chart\TableChart;

class SearchTerms extends AbstractReport
{
	/**
	 * Constructor.
	 *
	 * @param QueryBuilderFactory   $builderFactory
	 * @param UrlGenerator          $routingGenerator
	 */
	public function __construct(QueryBuilderFactory $builderFactory, UrlGenerator $routingGenerator)
	{
		parent::__construct($builderFactory, $routingGenerator);
		$this->name = 'search_terms';
		$this->displayName = 'Search Terms';
		$this->reportGroup = "Logs";
		$this->_charts = [new TableChart];
	}

	/**
	 * Retrieves JSON representation of the data and columns.
	 * Applies data to chart types set on report.
	 *
	 * @return Array  Returns all types of chart set on report with appropriate data.
	 */
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

	/**
	 * Set columns for use in reports.
	 *
	 * @return String  Returns columns in JSON format.
	 */
	public function getColumns()
	{
		$columns = [
			['type' => 'string', 	'name' => "Search Term",],
			['type' => 'number',	'name' => "Frequency",	],
		];

		return json_encode($columns);
	}

	/**
	 * Gets all searched made on the site.
	 *
	 * @return Query
	 */
	private function _getQuery()
	{
		$queryBuilder = $this->_builderFactory->getQueryBuilder();

		$queryBuilder
			->select('term AS "Term"')
			->select('COUNT(log_id) AS "Frequency"')
			->from('search_log')
			->groupBy('term')
			->orderBY('Frequency DESC')
		;

		return $queryBuilder->getQuery();
	}

	/**
	 * Takes the data and transforms it into either
	 *
	 * @param  $data    DB\Result  The data from the report query.
	 * @param  $output  String     The type of output required.
	 *
	 * @return String|Array  Returns columns as string in JSON format or array.
	 */
	private function _dataTransform($data)
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