<?php

namespace Message\Mothership\CMS\Page;

class Edit
{
	public function __construct(Query $query, UserInterface $currentUser)
	{

	}

	public function save(Page $page, Content $content = null)
	{

	}

	public function move(Page $page, Page $parent = null, $index = 0)
	{

	}

	public function publish(Page $page)
	{
		$page->authorship->update(new DateTimeImmutable, $this->_currentUser->id);

		$this->_query->run('
			UPDATE
				page
			SET
				publish_state = 1,
				publish_at    = :publishAt?i,
				updated_at    = :updatedAt?i,
				updated_by    = :updatedBy?i
			WHERE
				id = :id?i
		', array(
			'id' => $page->id,
		));

		// set publish_state to 1
		// if publish_at in the past, set it to NULL
	}

	public function unpublish(Page $page)
	{

	}
}