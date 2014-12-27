<?php

class Good extends Eloquent implements LemonTree\ElementInterface {

	use LemonTree\ElementTrait;

	public function getHref()
	{
		$category = $this->category;

		return \URL::route('catalogue', array(
			'url1' => $category->url,
			'url2' => $this->url
		));
	}

	public function getFolderHash()
	{
		return substr(md5(rand()), 0, 2);
	}

	public function category()
	{
		return $this->belongsTo('Category', 'category_id');
	}

	public function subcategory()
	{
		return $this->belongsTo('Subcategory', 'subcategory_id');
	}

	public function brand()
	{
		return $this->belongsTo('GoodBrand', 'good_brand_id');
	}

}
