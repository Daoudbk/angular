<?php namespace LemonTree;

class FavoritesController extends \BaseController {

	public function postToggle($classId)
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		$favorite = Favorite::where(
				function($query) use ($loggedUser, $classId) {
					$query->where('user_id', $loggedUser->id);
					$query->where('class_id', $classId);
				}
			)->
			orderBy('created_at')->first();

		if ($favorite) {
			$favorite->delete();
		} else {
			$favorite = new Favorite;
			$favorite->class_id = $classId;
			$favorite->user_id = $loggedUser->id;
			$favorite->save();
		}

		return $this->getList();
	}

	public function getList()
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		$favoriteList = Favorite::where('user_id', $loggedUser->id)->
			orderBy('created_at')->get();

		$favorites = [];

		foreach ($favoriteList as $k => $favorite) {
			$element = $favorite->getElement();
			if ( ! $element) {
				unset($favoriteList[$k]);
				continue;
			}
			$item = $element->getItem();
			$mainProperty = $item->getMainProperty();
			$favorites[] = [
				'id' => $favorite->id,
				'classId' => $element->getClassId(),
				'name' => $element->{$mainProperty},
			];
		}

		$scope['favoriteList'] = $favorites;

		return \Response::json($scope);
	}

}
