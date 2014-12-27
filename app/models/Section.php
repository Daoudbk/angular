<?php

class Section extends Eloquent implements LemonTree\ElementInterface {

	use LemonTree\ElementTrait;

	public function getHref()
	{
		return URL::route($this->url);
	}

}
