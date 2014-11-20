<?php

namespace Message\Mothership\CMS\Controller\Module\Blog;

use Message\Mothership\CMS\Blog\Comment;

use Message\Cog\Controller\Controller;

class Avatar extends Controller
{
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