<?php

namespace Message\Mothership\CMS\Bootstrap;

use Message\Cog\Bootstrap\TasksInterface;
use Message\Mothership\CMS\Task;

class Tasks implements TasksInterface
{
    public function registerTasks($tasks)
    {
        // Order related ports
        $tasks->add(new Task\Porting\SlugRedirects('cms:porting:slug_redirect'), 'Porting slugs');

    }
}