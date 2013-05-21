<?php

namespace Message\Mothership\CMS\Page;

class Content
{

}

// playing about
/*
$loader = new Page\ContentLoader($queryObject);

$content = $loader->load($page); // returns Page\Content

$content->specialTitle;
	// ->specialTitle is Page\Field\Field

$content->promoGroup->url;
	// ->promoGroup is Page\Field\Group

	// ->promoGroupRepeatable is Page\Field\Repeatable
foreach ($content->promoGroupRepeatable as $field_name => $promo) {
	$promo->title;
	$promo->image;
}

// data names
$content->productSelector; // return 4:5? do this magically?
	// ->productSelector is Page\Field\Field (with stuff set for the split data)

$content->productSelector->productID; // 4
$content->productSelector->colourID; // 5
*/