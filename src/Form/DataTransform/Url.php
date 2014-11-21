<?php

namespace Message\Mothership\CMS\Form\DataTransform;

use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

class Url implements DataTransformerInterface
{
	public function transform($url)
	{
		return $url;
	}

	public function reverseTransform($url)
	{
		if (null === $url) {
			return null;
		}

		if (!is_string($url)) {
			return new TransformationFailedException('URL could not be converted to a string');
		}

		if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
			return "http://" . $url;
		}

		return $url;
	}
}