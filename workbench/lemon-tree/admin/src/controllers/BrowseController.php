<?php namespace LemonTree;

class BrowseController extends \BaseController {

	public function getIndex($classId = null)
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		if ($classId) {

			$currentElement = Element::getByClassId($classId);

			if ( ! $currentElement) {
				$scope['state'] = 'error_element_not_found';
				return \Response::json($scope);
			}

			if ( ! $loggedUser->hasViewAccess($currentElement)) {
				$scope['state'] = 'error_element_access_denied';
				return \Response::json($scope);
			}

		} else {

			$currentElement = null;

		}

		$site = \App::make('site');

		$itemList = $site->getItemList();

		$elementListViewList = array();

		foreach ($itemList as $itemName => $item) {

			$elementListView = $this->getElementListView(
				$item, $currentElement
			);

			if (sizeof($elementListView)) {
				$elementListViewList[] = $elementListView;
			}

		}

		$scope['elementListViewList'] = $elementListViewList;

		return \Response::json($scope);
	}

	public function getBinds($classId = null)
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		if ($classId) {

			$currentElement = Element::getByClassId($classId);

			if ( ! $currentElement) {
				$scope['state'] = 'error_element_not_found';
				return \Response::json($scope);
			}

			if ( ! $loggedUser->hasViewAccess($currentElement)) {
				$scope['state'] = 'error_element_access_denied';
				return \Response::json($scope);
			}

		} else {

			$currentElement = null;

		}

		$site = \App::make('site');

		$itemList = $site->getItemList();

		$bindItemList = array();

		$binds = $site->getBinds();

		foreach ($itemList as $itemName => $item) {
			if (
				! $loggedUser->hasUpdateDefaultAccess($item)
			) continue;

			elseif (
				$currentElement
				&& isset($binds[$currentElement->getClass()][$itemName])
			) $bindItemList[] = [
				'name' => $item->getName(),
				'title' => $item->getTitle(),
			];

			elseif (
				$currentElement
				&& isset($binds[$currentElement->getClassId()][$itemName])
			) $bindItemList[] = [
				'name' => $item->getName(),
				'title' => $item->getTitle(),
			];

			elseif (
				! $currentElement
				&& isset($binds[Site::ROOT][$itemName])
			) $bindItemList[] = [
				'name' => $item->getName(),
				'title' => $item->getTitle(),
			];
		}

		$scope['bindItemList'] = $bindItemList;

		return \Response::json($scope);
	}

	private function getElementListView(
		Item $item,
		$currentElement = null
	)
	{
		$scope = array();

		$currentClassId = $currentElement
			? $currentElement->getClassId()
			: Site::ROOT;

		$parameters = array(
			'classId' => $currentClassId,
			'item' => $item->getName(),
			'expand' => true,
		);

		$loggedUser = LoggedUser::getUser();

		$propertyList = $item->getPropertyList();

		if ( ! $currentElement && ! $item->getRoot()) {
			return $scope;
		}

		if ($currentElement) {
			$flag = false;
			foreach ($propertyList as $propertyName => $property) {
				if (
					$currentElement
					&& $property instanceof OneToOneProperty
					&& $property->getRelatedClass() == $currentElement->getClass()
				) $flag = true;
			}
			if ( ! $flag) {
				return $scope;
			}
		}

		$itemPropertyList = [];

		foreach ($propertyList as $propertyName => $property) {
			if (
				! $property->getShow()
				|| $property->getHidden()
				|| $property->getName() === 'deleted_at'
			) continue;

			$itemPropertyList[] = [
				'name' => $property->getName(),
				'title' => $property->getTitle(),
				'class' => $property->getClassName(),
			];
		}

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

		$elementListCriteria = $item->getClass()->where(
			function($query) use ($propertyList, $currentElement) {
				if ($currentElement) {
					$query->orWhere('id', null);
				}
				foreach ($propertyList as $propertyName => $property) {
					if (
						$currentElement
						&& $property instanceof OneToOneProperty
						&& $property->getRelatedClass() == $currentElement->getClass()
					) {
						$query->orWhere(
							$property->getName(), $currentElement->id
						);
					} elseif (
						! $currentElement
						&& $property instanceof OneToOneProperty
					) {
						$query->orWhere(
							$property->getName(), null
						);
					}
				}
			}
		);

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

		if ( ! $total) {
			return $scope;
		}

		$lists = $loggedUser->getParameter('lists');
		$orders = $loggedUser->getParameter('orders');
		$pages = $loggedUser->getParameter('pages');

		$orderBy = isset($orders[$currentClassId][$item->getName()])
			? $orders[$currentClassId][$item->getName()]
			: null;

		$page = isset($pages[$currentClassId][$item->getName()])
			? $pages[$currentClassId][$item->getName()]
			: null;

		$browseFilterView = null;

		$copyAccessMap = array();
		$updateAccessMap = array();
		$deleteAccessMap = array();

		$orderByList = $item->getOrderByList();

		$currentOrderByList = array();

		if (
			isset($orderBy['field'])
			&& isset($orderBy['direction'])
			&& (
				! isset($orderByList[$orderBy['field']])
				|| $orderByList[$orderBy['field']] != $orderBy['direction']
				|| sizeof($orderByList) > 1
			)
		) {
			$elementListCriteria->orderBy(
				$orderBy['field'],
				$orderBy['direction']
			);
			$currentOrderByList[$orderBy['field']] = $orderBy['direction'];
			$defaultOrderBy = false;
		} else {
			foreach ($orderByList as $field => $direction) {
				$elementListCriteria->orderBy($field, $direction);
				$currentOrderByList[$field] = $direction;
			}
			$defaultOrderBy = true;
		}

		$site = \App::make('site');

		$perPage = $item->getPerPage();

		if ($perPage) {
			if ($page > ceil($total / $perPage)) {
				$page = ceil($total / $perPage);
			}
			\Paginator::setCurrentPage($page);
			$elementList = $elementListCriteria->paginate($perPage);
			$elementList->setBaseUrl(null);
			$elementList->appends($parameters);
			$scope['currentPage'] = $elementList->getCurrentPage();
			$scope['lastPage'] = $elementList->getLastPage();
		} else {
			$elementList = $elementListCriteria->get();
		}

		$elements = [];

		foreach ($elementList as $element) {
			$properties = [];

			foreach ($propertyList as $property) {
				if (
					! $property->getShow()
					|| $property->getHidden()
					|| $property->getName() === 'deleted_at'
				) continue;

				$property->setElement($element);

				$properties[] = [
					'name' => $property->getName(),
					'title' => $property->getTitle(),
					'class' => $property->getClassName(),
					'readonly' => $property->getReadonly(),
					'isMainProperty' => $property->isMainProperty(),
					'item' => [
						'name' => $item->getName(),
						'title' => $item->getTitle(),
					],
					'listView' => $property->getListView(),
				];
			}

			$elements[] = [
				'id' => $element->id,
				'class' => $element->getClass(),
				'classId' => $element->getClassId(),
				'propertyList' => $properties,
			];
		}

		if ( ! $loggedUser->isSuperUser()) {

			foreach ($groupList as $group) {

				$itemPermission = $group->getItemPermission($item->getName())
					? $group->getItemPermission($item->getName())->permission
					: $group->default_permission;

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

				foreach ($elementList as $element) {
					if (isset($elementPermissionMap[$element->id])) {
						if ($elementPermissionMap[$element->id] == 'delete') {
							$copyAccessMap[$element->id] = $element->id;
							$updateAccessMap[$element->id] = $element->id;
							$deleteAccessMap[$element->id] = $element->id;
						} elseif ($elementPermissionMap[$element->id] == 'update') {
							$updateAccessMap[$element->id] = $element->id;
						}
					} else {
						if ($itemPermission == 'delete') {
							$copyAccessMap[$element->id] = $element->id;
							$updateAccessMap[$element->id] = $element->id;
							$deleteAccessMap[$element->id] = $element->id;
						} elseif ($itemPermission == 'update') {
							$copyAccessMap[$element->id] = $element->id;
							$updateAccessMap[$element->id] = $element->id;
						}
					}
				}

			}

		} else {

			foreach ($elementList as $element) {
				$copyAccessMap[$element->id] = $element->id;
				$updateAccessMap[$element->id] = $element->id;
				$deleteAccessMap[$element->id] = $element->id;
			}

		}

		$scope['currentElement'] = $currentElement;
		$scope['classId'] = $currentClassId;
		$scope['item'] = [
			'name' => $item->getName(),
			'title' => $item->getTitle()
		];
		$scope['currentOrderByList'] = $currentOrderByList;
		$scope['defaultOrderBy'] = $defaultOrderBy;
		$scope['itemPropertyList'] = $itemPropertyList;
		$scope['total'] = $total;
		$scope['elementList'] = $elements;
		$scope['copyAccessMap'] = $copyAccessMap;
		$scope['updateAccessMap'] = $updateAccessMap;
		$scope['deleteAccessMap'] = $deleteAccessMap;

		return $scope;
	}

}
