<?php

namespace Message\Mothership\CMS\FormType\DataTransform;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use Message\Cog\ValueObject\Slug as SlugObject;

class Slug implements DataTransformerInterface
{
	/**
	 * @param Slug $slug                            Instance of Slug object to convert to array
	 * @throws TransformationFailedException        Throws exception if Slug object not given
	 *
	 * @return array                                Returns array of slug segments
	 */
	public function transform($slug)
	{
		if (!$slug instanceof SlugObject) {
			throw new TransformationFailedException('Could not transform slug to array, instance of Message\\Cog\\ValueObject\\Slug must be given');
		}

		return (array) $slug->getSegments();
	}

	/**
	 * @param string | array $slug              Slug data submitted by form
	 * @throws TransformationFailedException    Exception thrown if invalid type given
	 *
	 * @return SlugObject                       Returns instance of Slug object
	 */
	public function reverseTransform($slug)
	{
		if (is_string($slug)) {
			return new SlugObject($slug);
		}
		elseif (is_array($slug)) {
			return new SlugObject($this->_parseArray($slug));
		}

		throw new TransformationFailedException('Could not transform slug, must be either a string or an array');
	}

	/**
	 * Parse array to make valid, i.e. explodes a string or any array values and makes into a
	 * one dimensional array
	 *
	 * @param array $slug
	 *
	 * @return array
	 */
	protected function _parseArray(array $slug)
	{
		$trueSlug = array();

		foreach ($slug as $segment) {
			$segment = explode('/', $segment);
			foreach ($segment as $value) {
				$trueSlug[] = $value;
			}
		}

		return $trueSlug;
	}
}