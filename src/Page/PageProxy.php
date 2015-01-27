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
	private $_metaImageID;

	public function __construct(EntityLoaderCollection $loaders)
	{
		$this->_loaders = $loaders;
	}

	public function getTags()
	{
		if (!$this->_isLoaded('tags')) {
			$tags = $this->_loaders->get('tags')->load($this);
			
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

	/**
	 * {@inheritDoc}
	 */
	public function getMetaImage()
	{
		if (!$this->_isLoaded('metaImage')) {
			$image = $this->_loaders->get('image')->getById($this->_metaImageID);
		}

		return parent::getMetaImage();
	}

	protected function _isLoaded($entityName)
	{
		return in_array($entityName, $this->_loaded);
	}
}