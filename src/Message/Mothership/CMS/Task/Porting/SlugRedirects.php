<?php

namespace Message\Mothership\CMS\Task\Porting;

use Message\Cog\Console\Task\Task as BaseTask;
use Message\Cog\Filesystem\File as FileSystemFile;
use Symfony\Component\Console\Input\InputArgument;

class SlugRedirects extends BaseTask
{

	protected function configure()
	{
		$this
			->addArgument(
				'path',
				InputArgument::REQUIRED,
				'please pass in the name of the service as the last parameter'
			);
	}
	/**
	 * Gets the DB connection to port the data into
	 *
	 * @return Connection 		instance of the DB Connection
	 */
	public function getToConnection()
	{
		return new \Message\Cog\DB\Adapter\MySQLi\Connection(array(
				'host'		=> $this->get('cfg')->db->hostname,
				'user'		=> $this->get('cfg')->db->user,
				'password' 	=> $this->get('cfg')->db->pass,
				'db'		=> $this->get('cfg')->db->name,
				'charset'	=> 'utf-8',
		));
	}

    public function process()
    {
		$uwNew = $this->getToConnection();

		$new = new \Message\Cog\DB\Transaction($uwNew);

		$file = new FileSystemFile($this->getRawInput()->getArgument('path'));
		$file = $file->openFile('rw');

		$i = 0;
		$addedCount = 0;
		while(!$file->eof() && $data = $file->fgetcsv()) {
			$i++;

			if ($i == 1) {
				continue;
			}

			$row = array(
				'old_slug' => str_replace('/','',$data[0]),
				'new_slug' => $data[1],
				'page_id'  => $data[2],
			);

			if ($row['page_id']) {
				$page = $this->get('cms.page.loader')->includeUnpublished(true)->getByID((int) $row['page_id']);
			} elseif ($row['new_slug']) {
				$page = $this->get('cms.page.loader')->includeUnpublished(true)->getBySlug($data['new_slug']);
			} else {
	        	$this->writeln('<error>Please supply either a new slug or pageID</error>');
				continue;
			}

			if (!$page) {
				// output error if cannot find the page
	        	$this->writeln('<error>Couldn\'t find page for '.($data[3] ? 'pageID:'.$data[3] : 'slug '. $data[1]).'</error>');
			}

			$datetime = new \DateTime;
			$result = $new->add('
				REPLACE INTO
					page_slug_history
				SET
					page_id = ?i,
					slug 	= ?s,
					created_at = ?d,
					created_by = ?i',
				array(
					$page->id,
					'/'.$row['old_slug'],
					$datetime->getTimestamp(),
					$this->get('user.current')->id,
				)
			);
			$addedCount++;
		}

		if ($new->commit()) {
        	$this->writeln('<info>Successfully ported '.$addedCount.' slugs</info>');
		}

		return $ouput;
    }
}