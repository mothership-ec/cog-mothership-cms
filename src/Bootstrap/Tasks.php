<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Message\Mothership\CMS\Task;

class Tasks implements TasksInterface
{
	public function registerTasks($tasks)
	{
		$tasks->add(new Task\FixPageTree('cms:page:fix_tree'), 'Fixes the page nested set tree (siblings and parents)');
	}
}