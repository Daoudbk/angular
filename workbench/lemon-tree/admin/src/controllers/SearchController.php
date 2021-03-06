<?php namespace LemonTree;

use Carbon\Carbon;

class SearchController extends \BaseController {

	public function getItems()
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		$site = \App::make('site');

		$sort = \Input::get('sort');

		$search = $loggedUser->getParameter('search') ?: [];

		$currentItem = isset($search['currentItem'])
			? $site->getItemByName($search['currentItem'])
			: null;

		if (in_array($sort, array('rate', 'date', 'name', 'default'))) {
			$search['sortItem'] = $sort;
			$loggedUser->setParameter('search', $search);
		}

		$sortItem =
			isset($search['sortItem'])
			? $search['sortItem']
			: 'default';

		$itemList = $site->getItemList();

		$map = array();

		if ($sortItem == 'name') {

			foreach ($itemList as $item) {
				$map[$item->getTitle()] = $item;
			}

			ksort($map);

		} elseif ($sortItem == 'date') {

			$sortItemDate = isset($search['sortItemDate'])
				? $search['sortItemDate'] : array();

			arsort($sortItemDate);

			foreach ($sortItemDate as $class => $date) {
				$map[$class] = $site->getItemByName($class);
			}

			foreach ($itemList as $item) {
				$map[$item->getName()] = $item;
			}

		} elseif ($sortItem == 'rate') {

			$sortItemRate = isset($search['sortItemRate'])
				? $search['sortItemRate'] : array();

			arsort($sortItemRate);

			foreach ($sortItemRate as $class => $rate) {
				$map[$class] = $site->getItemByName($class);
			}

			foreach ($itemList as $item) {
				$map[$item->getName()] = $item;
			}

		} else {

			foreach ($itemList as $item) {
				$map[] = $item;
			}

		}

		$items = array();

		foreach ($map as $item) {
			$items[] = [
				'name' => $item->getName(),
				'title' => $item->getTitle(),
			];
		}

		unset($map);

		$scope['sortItem'] = $sortItem;
		$scope['itemList'] = $items;
		$scope['currentItem'] = $currentItem
			? [
				'name' => $currentItem->getName(),
				'title' => $currentItem->gettitle(),
			] : null;

		return \Response::json($scope);
	}

	public function getItem($class)
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		$site = \App::make('site');

		$item = $site->getItemByName($class);

		if ( ! $item) {
			$scope['state'] = 'error_search_item_not_found';
			return \Response::json($scope);
		}

		$search = is_array($loggedUser->getParameter('search'))
			? $loggedUser->getParameter('search') : [];

		$sort = \Input::get('sort');

		if (in_array($sort, array('rate', 'date', 'name', 'default'))) {

			if (
				! isset($search['sortProperty'])
				|| ! is_array($search['sortProperty'])) {
				$search['sortProperty'] = [];
			}

			$search['sortProperty'][$class] = $sort;

		} else {

			if (
				! isset($search['sortItemDate'])
				|| ! is_array($search['sortItemDate'])
			) {
				$search['sortItemDate'] = [];
			}

			if (
				! isset($search['sortItemRate'])
				|| ! is_array($search['sortItemRate'])) {
				$search['sortItemRate'] = [];
			}

			$search['currentItem'] = $class;

			$search['sortItemDate'][$class] =
				Carbon::now()->toDateTimeString();

			if (isset($search['sortItemRate'][$class])) {
				$search['sortItemRate'][$class]++;
			} else {
				$search['sortItemRate'][$class] = 1;
			}

		}

		$loggedUser->setParameter('search', $search);

		$sortProperty =
			isset($search['sortProperty'][$item->getName()])
			? $search['sortProperty'][$item->getName()]
			: 'default';

		$propertyList = $item->getPropertyList();

		foreach ($propertyList as $propertyName => $property) {
			if (
				$property->getHidden()
				|| $propertyName == 'deleted_at'
			) {
				unset($propertyList[$propertyName]);
			}
		}

		if ($sortProperty == 'name') {

			$map = array();

			foreach ($propertyList as $property) {
				$map[$property->getTitle()] = $property;
			}

			ksort($map);

			$properties = array();

			foreach ($map as $property) {
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
					'searchView' => $property->getSearchView(),
				];
			}

			unset($map);

		} elseif ($sortProperty == 'date') {

			$sortPropertyDate =
				isset($search['sortPropertyDate'][$class])
				? $search['sortPropertyDate'][$class]
				: array();

			arsort($sortPropertyDate);

			$map = array();

			foreach ($sortPropertyDate as $propertyName => $date) {
				$map[$propertyName] = $item->getPropertyByName($propertyName);
			}

			foreach ($propertyList as $property) {
				$map[$property->getName()] = $property;
			}

			$properties = array();

			foreach ($map as $property) {
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
					'searchView' => $property->getSearchView(),
				];
			}

			unset($map);

		} elseif ($sortProperty == 'rate') {

			$sortPropertyRate =
				isset($search['sortPropertyRate'][$class])
				? $search['sortPropertyRate'][$class]
				: array();

			arsort($sortPropertyRate);

			$map = array();

			foreach ($sortPropertyRate as $propertyName => $rate) {
				$map[$propertyName] = $item->getPropertyByName($propertyName);
			}

			foreach ($propertyList as $property) {
				$map[$property->getName()] = $property;
			}

			$properties = array();

			foreach ($map as $property) {
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
					'searchView' => $property->getSearchView(),
				];
			}

			unset($map);

		} else {

			$properties = array();

			foreach ($propertyList as $property) {
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
					'searchView' => $property->getSearchView(),
				];
			}

		}

		if ($item) {
			$scope['item'] =  [
				'name' => $item->getName(),
				'title' => $item->getTitle(),
			];

			$scope['sortProperty'] = $sortProperty;
			$scope['propertyList'] = $properties;
		}

		return \Response::json($scope);
	}

	public function getList()
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		$site = \App::make('site');

		$class = \Input::get('class');

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

}
