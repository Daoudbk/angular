<?php

class Category extends Eloquent implements LemonTree\ElementInterface {

	use LemonTree\ElementTrait;

	public function getHref()
	{
		return \URL::route('catalogue', array('url' => $this->url));
	}

}
