<?php

namespace Message\Mothership\CMS\Report;

use Message\Cog\DB\QueryBuilderInterface;
use Message\Report\ReportInterface;
use Message\Mothership\Report\Report\AbstractReport;
use Message\Cog\DB\QueryBuilderFactory;
use Message\Mothership\Report\Chart\TableChart;
use Message\Mothership\Report\Filter\DateFilter;

class SearchTerms extends AbstractReport
{
	private $_to = [];
	private $_from = [];
	private $_builderFactory;
	private $_charts;
	private $_filters;

	public function __construct(QueryBuilderFactory $builderFactory)
	{
		$this->name = "search-terms-report";
		$this->_builderFactory = $builderFactory;
		$this->_charts = [new TableChart];
		$this->_filters = [new DateFilter];
	}

	public function getName()
	{
		return $this->name;
	}

	public function getCharts()
	{
		$data = $this->dataTransform($this->getQuery()->run());

		foreach ($this->_charts as $chart) {
			$chart->setData($data);
		}

		return $this->_charts;
	}

	/**
	 * Gets the query as an string
	 *
	 * @param  string   $date         Unix date from which to get data
	 * @param  bool     $today        If date selected is current day
	 *
	 * @return string
	 */
	public function getQuery()
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

		//de($queryBuilder->getQuery());

		return $queryBuilder->getQuery();
	}

	protected function dataTransform($data)
	{
		$result = [];
		$result[] = $data->columns();

		foreach ($data as $row) {
			$result[] = get_object_vars($row);

		}

		return $result;
	}
}