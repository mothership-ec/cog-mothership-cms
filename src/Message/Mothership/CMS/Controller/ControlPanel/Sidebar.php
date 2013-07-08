<?php

namespace Message\Mothership\CMS\Controller\ControlPanel;

class Sidebar extends \Message\Cog\Controller\Controller
{
	public function index()
	{
		$loader = $this->_services['cms.page.loader'];
		$pages  = $loader->getAll();
		$values = $this->buildTree($pages);

		return $this->render('Message:Mothership:CMS::sidebar', array(
			'tree' => $values,
		));
	}

	/**
	 * Build the full page tree and return a nice array
	 *
	 * @param  array  	$arr 		array of page objects
	 * @param  Page  	$prev_sub  	loop of the page which is recurssive
	 * @param  integer 	$cur_depth 	The current recurrsive depth
	 *
	 * @return array 				array of nested page objects
	 */
	public function buildTree(&$arr, &$prev_sub = null, $cur_depth = 0) {
		$cur_sub = array();
		while (!empty($arr) && ($line = current($arr))) {
			if ($line->depth < $cur_depth) {
				return $cur_sub;
			} elseif ($line->depth > $cur_depth) {
				$prev_sub = $this->buildTree($arr, $cur_sub, $cur_depth + 1);
			} else {
				$cur_sub[$line->id] = $line;
				$prev_sub =& $cur_sub[$line->id]->children;
				next($arr);
			}
		}

		return $cur_sub;
	}

}