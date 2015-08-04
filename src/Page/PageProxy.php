<?php

namespace Message\Mothership\CMS\Page;

use Message\Cog\DB\Entity\EntityLoaderCollection;

/**
 * Represents the properties of a single page.
 *
 * @author Joe Holdcroft <joe@message.co.uk>
 *
 */
class PageProxy extends Page
{
	protected $_loaders;
	protected $_loaded = [];

	public function __construct(EntityLoaderCollection $loaders)
	{
		$this->_loaders = $loaders;
	}

	public function getTags()
	{
		if (!$this->_isLoaded('tags')) {
			$tags = $this->_loaders->get('tags')->load($this, true, true);
			
			if ($tags !== false) {
				$this->tags = $this->tags + $tags;
			}

			$this->_loaded[] = 'tags';
		}

		return parent::getTags();
	}

	public function hasTag($tag)
	{
		return in_array($tag, $this->getTags());
	}

	public function getContent()
	{
		if (!$this->_isLoaded('content')) {
			$content = $this->_loaders->get('content')->load($this);
			
			if ($content !== false) {
				$this->_content = $content;
			}

			$this->_loaded[] = 'content';
		}

		return parent::getContent();
	}

	public function setMetaImage($file)
	{
			parent::setMetaImage($file);

			$this->_loaded[] = 'metaImage';
	}

	/**
	 * {@inheritDoc}
	 */
	public function getMetaImage()
	{
		if (!$this->_isLoaded('metaImage')) {
			$image = $this->_loaders->get('image')->load($this);
			$this->setMetaImage($image);
			$this->_loaded[] = 'metaImage';
		}

		return parent::getMetaImage();
	}

	protected function _isLoaded($entityName)
	{
		return in_array($entityName, $this->_loaded);
	}
}