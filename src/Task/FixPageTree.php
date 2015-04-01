<?php

namespace Message\Mothership\CMS\Task;

use Message\Cog\Console\Task\Task;

class FixPageTree extends Task
{
	private $_pageLoader;
	private $_query;
	private $_nestedSetHelper;

	public function process()
	{
		$this->_query = $this->get('db.query');
		$this->_nestedSetHelper = $this->get('cms.page.nested_set_helper');

		$this->_pageLoader = $this->get('cms.page.loader');
		$this->_pageLoader->includeDeleted(true);
		$this->_pageLoader->includeUnpublished(true);

		$this->writeln('<info>Building page tree</info>');
		$tree = $this->_buildTree();
		$this->writeln('<info>Clearing tree data</info>');
		$this->_query->run($this->_clearTreeDataQuery());
		$this->writeln('<info>Refreshing nested set data</info>');
		$this->_buildNestedSet($tree);
		$this->writeln('<info>Refreshing orphaned pages</info>');
		$this->_resetOrphanedPages();
	}

	private function _resetOrphanedPages()
	{
		$orphanedPages = $this->_query->run('SELECT `page_id` FROM `page` WHERE `position_left` IS NULL;')->flatten();
		$this->writeln(count($orphanedPages) . ' orphaned pages found');

		foreach ($orphanedPages as $page) {
			$this->writeln('Reseting page ' . $page);
			$this->_nestedSetHelper->insertChildAtEnd($page, null, true)->commit();
		}
	}

	private function _buildNestedSet($node)
	{
		foreach ($node->children as $child) {
			$this->writeln('Inserting page ' . $child->page . ' into parent ' . ($node->page ?: 'root'));
			$this->_nestedSetHelper->insertChildAtEnd($child->page, $node->page, true)->commit();
			$this->_buildNestedSet($child);
		}
	}

	private function _clearTreeDataQuery()
	{
		return "UPDATE `page` SET `position_left` = NULL, `position_right` = NULL, `position_depth` = NULL;";
	}

	private function _buildTree()
	{
		$root = new \stdClass;
		$root->page = null;
		$root->children = $this->_buildChildren($this->_pageLoader->getTopLevel());

		return $root;
	}

	private function _buildChildren($nodes)
	{
		return array_map(function($x) {
			$node = new \stdClass;
			$node->page = $x->id;
			$node->children = $this->_buildChildren($this->_pageLoader->getChildren($x));
			return $node;
		}, $nodes);
	}
}