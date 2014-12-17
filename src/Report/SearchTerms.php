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
		$this->description =
			"This report shows all searches made on the site.";
	}

	/**
	 * Retrieves JSON representation of the data and columns.
	 * Applies data to chart types set on report.
	 *
	 * @return array  Returns all types of chart set on report with appropriate data.
	 */
	public function getCharts()
	{
		$data = $this->_dataTransform($this->_getQuery()->run(), "json");
		$columns = $this->_parseColumns($this->getColumns());

		foreach ($this->_charts as $chart) {
			$chart->setColumns($columns);
			$chart->setData($data);
		}

		return $this->_charts;
	}

	/**
	 * Set columns for use in reports.
	 *
	 * @return array  Returns array of columns as keys with format for Google Charts as the value.
	 */
	public function getColumns()
	{
		return [
			'Search Term' => 'string',
			'Frequency'   => 'number',
		];
	}

	/**
	 * Gets all searched made on the site.
	 *
	 * @return Query
	 */
	protected function _getQuery()
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
	 * Takes the data and transforms it into a useable format.
	 *
	 * @param  $data    DB\Result  The data from the report query.
	 * @param  $output  string     The type of output required.
	 *
	 * @return string|array  Returns data as string in JSON format or array.
	 */
	protected function _dataTransform($data, $output = null)
	{
		$result = [];

		if ($output === "json") {

			foreach ($data as $row) {
				$result[] = [
					$row->Term,
					$row->Frequency,
				];
			}
			return json_encode($result);

		} else {

			foreach ($data as $row) {
				$result[] = [
					$row->Term,
					$row->Frequency,
				];
			}
			return $result;
		}
	}
}