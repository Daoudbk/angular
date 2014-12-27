<?php

class Subcategory extends Eloquent implements LemonTree\ElementInterface {

	use LemonTree\ElementTrait;

	public function getHref()
	{
		$category = $this->category;

		return \URL::route('catalogue', array(
			'url1' => $category->url,
			'url2' => $this->url
		));
	}

	public function category()
	{
		return $this->belongsTo('Category', 'category_id');
	}

}
