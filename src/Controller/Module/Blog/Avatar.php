<?php

namespace Message\Mothership\CMS\Controller\Module\Blog;

use Message\Mothership\CMS\Blog\Comment;

use Message\Cog\Controller\Controller;

/**
 * Class Avatar
 * @package Message\Mothership\CMS\Controller\Module\Blog
 *
 * @author Thomas Marchant <thomas@message.co.uk>
 */
class Avatar extends Controller
{
	/**
	 * Render avatar to display next to comment
	 *
	 * @param Comment $comment
	 * @param int $size
	 * @param null $default
	 *
	 * @return \Message\Cog\HTTP\Response
	 */
	public function renderAvatar(Comment $comment, $size = 50, $default = null)
	{
		$avatar = $this->get('avatar.provider.collection')
			->get($this->get('cfg')->blog->avatarProvider)
			->getAvatar($comment->getEmail(), $size, $default)
		;

		return $this->render('Message:Mothership:CMS::modules:blog:avatar', [
			'avatar' => $avatar,
		]);
	}
}