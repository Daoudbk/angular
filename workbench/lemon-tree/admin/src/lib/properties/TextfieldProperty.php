<?php namespace LemonTree;

class TextfieldProperty extends BaseProperty {

	public static function create($name)
	{
		return new self($name);
	}

	public function getSearchView()
	{
		$scope = parent::getSearchView();

		$relatedClass = $this->getItemName();

		$scope['relatedClass'] = $relatedClass;

		return $scope;
	}

}
