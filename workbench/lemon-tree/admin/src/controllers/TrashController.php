<?php namespace LemonTree;

use Carbon\Carbon;

class TrashController extends \BaseController {

	public function getItems()
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		$site = \App::make('site');

		$itemList = $site->getItemList();

		$itemCountList = array();

		foreach ($itemList as $itemName => $item) {
			$itemCount = $this->getItemCount(
				$item
			);

			if ($itemCount) {
				$itemCountList[] = [
					'name' => $item->getName(),
					'title' => $item->getTitle(),
					'total' => $itemCount,
				];
			}
		}

		$scope['itemList'] = $itemCountList;

		return \Response::json($scope);
	}

	public function getItem($class)
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		$site = \App::make('site');

		$item = $site->getItemByName($class);

		if ( ! $item) {
			$scope['state'] = 'error_trash_item_not_found';
			return \Response::json($scope);
		}

		$scope['item'] =  [
			'name' => $item->getName(),
			'title' => $item->getTitle(),
		];

		return \Response::json($scope);
	}

	protected function getItemCount(Item $item)
	{
		$loggedUser = LoggedUser::getUser();

		$propertyList = $item->getPropertyList();

		if ( ! $loggedUser->isSuperUser()) {

			$permissionDenied = true;
			$deniedElementList = array();
			$allowedElementList = array();

			$groupList = $loggedUser->getGroups();

			foreach ($groupList as $group) {

				$itemPermission = $group->getItemPermission($item->getName())
					? $group->getItemPermission($item->getName())->permission
					: $group->default_permission;

				if ($itemPermission != 'deny') {
					$permissionDenied = false;
					$deniedElementList = array();
				}

				$elementPermissionList = $group->elementPermissions;

				$elementPermissionMap = array();

				foreach ($elementPermissionList as $elementPermission) {
					$classId = $elementPermission->class_id;
					$permission = $elementPermission->permission;
					list($class, $id) = explode(Element::ID_SEPARATOR, $classId);
					if ($class == $item->getName()) {
						$elementPermissionMap[$id] = $permission;
					}
				}

				foreach ($elementPermissionMap as $id => $permission) {
					if ($permission == 'deny') {
						$deniedElementList[$id] = $id;
					} else {
						$allowedElementList[$id] = $id;
					}
				}

			}

		}

		$elementListCriteria = $item->getClass()->onlyTrashed();

		if ( ! $loggedUser->isSuperUser()) {

			if (
				$permissionDenied
				&& sizeof($allowedElementList)
			) {
				$elementListCriteria->whereIn('id', $allowedElementList);
			} elseif (
				! $permissionDenied
				&& sizeof($deniedElementList)
			) {
				$elementListCriteria->whereNotIn('id', $deniedElementList);
			} elseif ($permissionDenied) {
				return $scope;
			}

		}

		$total = $elementListCriteria->count();

		return $total;
	}

}
