<?php namespace LemonTree;

class BrowseController extends \BaseController {

	public function getIndex()
	{
		$scope = array();

		$categoryList = \Category::orderBy('order')->get();

		$scope['categoryList'] = $categoryList;

		return \Response::json($scope);
	}

}
