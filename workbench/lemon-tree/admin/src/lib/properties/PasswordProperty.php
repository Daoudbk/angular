<?php namespace LemonTree;

class PasswordProperty extends BaseProperty {

	public static function create($name)
	{
		return new self($name);
	}

	public function getElementSearchView()
	{
		return null;
	}

}
