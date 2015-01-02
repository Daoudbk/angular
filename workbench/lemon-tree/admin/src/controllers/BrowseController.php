<?php namespace LemonTree;

class BrowseController extends \BaseController {

	public function getIndex()
	{
		$scope = array();

		$categoryList = \Category::orderBy('order')->get();

		foreach ($categoryList as $category) {
			$category->classId = $category->getClassId();
		}

		$scope['categoryList'] = $categoryList;

		return \Response::json($scope);
	}

}
