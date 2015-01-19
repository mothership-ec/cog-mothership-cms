<?php

namespace Message\Mothership\CMS\Form\EventListener;

use Message\Mothership\CMS\Blog;
use Message\Cog\Event\SubscriberInterface;

use Symfony\Component\Form;

/**
 * Class ManageCommentsEventListener
 * @package Message\Mothership\CMS\Form\EventListener
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 *
 * Event listener for ensuring that using the ManageComments form will not fail validation if a user submits a comment
 * while the admin is using this form.
 */
class ManageCommentsEventListener implements SubscriberInterface
{
	/**
	 * @var \Message\Mothership\CMS\Blog\CommentCollection
	 */
	private $_comments;

	public function __construct(Blog\CommentCollection $comments)
	{
		$this->_comments = $comments;
	}

	/**
	 * {@inheritDoc}
	 */
	public static function getSubscribedEvents()
	{
		return [
			Form\FormEvents::PRE_SUBMIT => ['addMissingCommentData'],
		];
	}

	/**
	 * Loop through comments and add in any missing data. Default comments to 'pending' status
	 *
	 * @param Form\FormEvent $event
	 */
	public function addMissingCommentData(Form\FormEvent $event)
	{
		$data = $event->getData();
		foreach ($this->_comments as $id => $comment) {
			if (!array_key_exists('comment_' . $id, $data)) {
				$data['comment_' . $id] = Blog\Statuses::PENDING;
			}
		}
		$event->setData($data);
	}
}