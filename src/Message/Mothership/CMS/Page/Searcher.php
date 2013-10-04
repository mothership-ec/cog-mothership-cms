<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\DB\Query as DBQuery;
use InvalidArgumentException;

class Searcher {

	protected $_query;

	protected $_minTermLength;
	protected $_searchFields;
	protected $_fieldModifiers;
	protected $_pageTypeModifiers;
	protected $_excerptField;
	protected $_terms;

	protected $_scores;

	public function __construct(DBQuery $query, $markdown)
	{
		$this->_query    = $query;
		$this->_markdown = $markdown;
	}

	public function setMinTermLength($length)
	{
		$this->_minTermLength = $length;
	}

	public function setSearchFields($fields)
	{
		$this->_searchFields = (array) $fields;
	}

	public function setFieldModifiers($modifiers)
	{
		$this->_fieldModifiers = (array) $modifiers;
	}

	public function setPageTypeModifiers($modifiers)
	{
		$this->_pageTypeModifiers = (array) $modifiers;
	}

	public function setExcerptField($field)
	{
		$this->_excerptField = $field;
	}

	public function setTerms($terms)
	{
		$this->_terms = (array) $terms;
	}

	/**
	 * Get the ids of pages that match the search terms.
	 *
	 * @return array
	 */
	public function getIDs()
	{
		if (false === $query = $this->_buildQuery()) {
			return array();
		}

		$results = $this->_query->run($query[0], $query[1]);

		$this->_scores = $this->getScores($results);

		return array_unique($results->flatten());
	}

	/**
	 * Get the scores for a set of results.
	 *
	 * @param  array $results
	 *
	 * @return array
	 */
	public function getScores($results)
	{
		$scores = array();

		foreach ($results as $i => $result) {
			if (! isset($scores[$result->page_id])) {
				$scores[$result->page_id] = array(
					'total' => 0,
					'row'   => array(
						'score'   => 0,
						'excerpt' => null
					),
				);
			}

			$rowScore = 0;

			// Apply field modifiers.
			foreach ($this->_fieldModifiers as $field => $modifier) {
				foreach ($this->_terms as $term) {
					$rowScore += substr_count(strtolower($result->$field), $term) * $modifier;
				}
			}

			// Apply page type modifiers.
			foreach ($this->_pageTypeModifiers as $type => $modifier) {
				if ($result->type == $type) {
					$rowScore *= $modifier;
				}
			}

			$scores[$result->page_id]['total'] += $rowScore;

			if ($rowScore > $scores[$result->page_id]['row']['score']) {
				$scores[$result->page_id]['row'] = array(
					'score'   => $rowScore,
					'excerpt' => $this->_getExcerpt($result)
				);
			}
		}

		return $scores;
	}

	/**
	 * Get the sorted results.
	 *
	 * @param  array $results
	 *
	 * @return array
	 */
	public function getSorted($results)
	{
		$scores = $this->_scores;

		// Sort the pages by the score, and save the score against the page.
		uasort($results, function($a, $b) use ($scores) {
			// Save a and b to ensure no page is missed.
			$a->score = $scores[$a->id]['total'];
			$b->score = $scores[$b->id]['total'];

			$a->excerpt = $scores[$a->id]['row']['excerpt'];
			$b->excerpt = $scores[$b->id]['row']['excerpt'];

			// Return comparison.
			return $b->score - $a->score;
		});

		return $results;
	}

	/**
	 * Build the SQL query.
	 *
	 * @return string
	 */
	protected function _buildQuery()
	{
		if (null === $this->_minTermLength) {
			throw new InvalidArgumentException("Could not build search query, minTermLength must be set");
		}

		if (null === $this->_searchFields) {
			throw new InvalidArgumentException("Could not build search query, searchFields must be set");
		}

		$query = "(";
		$searchParams = array();

		// Loop terms and build query against each one.
		// Terms are lowered to ensure they are case-insensitive.
		foreach ($this->_terms as $i => $term) {
			if (strlen($term) >= $this->_minTermLength) {
				$this->_terms[$i] = $term = strtolower($term);

				foreach ($this->_searchFields as $j => $field) {
					$query .= 'LOWER(' . $field . ') LIKE :term' . $i . ' OR ';
				}

				$searchParams['term' . $i] = '%' . $term . '%';
			}
		}

		// Return false if none of the terms were long enough.
		if (count($searchParams) == 0) {
			return false;
		}

		// Remove the trailing ' OR '.
		$query = substr($query, 0, -4);

		$query .= ")";

		// Get the search results using a full outer join, built as a union of
		// a left and a right join. This allows every `page_content` row for
		// every `page` to be selected and iterated to add to the page's score.
		$query = '
			SELECT
				page.page_id,
				page.type,
				' . implode(', ', $this->_searchFields) . '
			FROM
				page
			LEFT JOIN
				page_content ON page_content.page_id = page.page_id
			WHERE
				visibility_search = 1 AND
				' . $query . '
			UNION
				SELECT
					page.page_id,
					page.type,
					' . implode(', ', $this->_searchFields) . '
				FROM
					page
				RIGHT JOIN
					page_content ON page_content.page_id = page.page_id
				WHERE
					visibility_search = 1 AND
					' . $query . '
		';

		return array($query, $searchParams);
	}

	/**
	 * Get the excerpt from a row.
	 *
	 * @param  object $row
	 *
	 * @return string
	 */
	public function _getExcerpt($row)
	{
		$excerpt = $row->{$this->_excerptField};

		// Transform markdown to allow easier cleaning
		$excerpt = $this->_markdown->transformMarkdown($excerpt);

		// Clean out HTML
		$excerpt = strip_tags($excerpt);

		// Trim the excerpt to a maximum of 75 words
		$maxWords = 75;
		$words = explode(' ', $excerpt, $maxWords);

		// If the last value contains a space, the word count was greater than
		// the limit and should be trimed and an ellipsis appended.
		if (false !== strpos($words[count($words)-1], ' ')) {
			array_pop($words);
			$words[] = '...';
		}

		// Recombine the excerpt
		$excerpt = implode(' ', $words);

		// Clean excerpt
		$excerpt = str_replace("\n", "", $excerpt);
		$excerpt = preg_replace('/ +/', ' ', $excerpt);
		$excerpt = trim($excerpt);

		return $excerpt;
	}

}