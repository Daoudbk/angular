<?php

define('test', false);

Route::filter('admin.auth', function() {

	if (defined('test') && test === true) {

		$userId = 1;

	} else {
		if ( ! function_exists('apache_request_headers')) {
			function apache_request_headers() {
				$arh = array();
				$rx_http = '/\AHTTP_/';
				foreach ($_SERVER as $key => $val) {
					if (preg_match($rx_http, $key)) {
						$arh_key = preg_replace($rx_http, '', $key);
						$rx_matches = array();
						$rx_matches = explode('_', $arh_key);
						if (sizeof($rx_matches) > 0 and strlen($arh_key) > 2) {
							foreach ($rx_matches as $ak_key => $ak_val) {
								$rx_matches[$ak_key] = ucfirst($ak_val);
							}
							$arh_key = implode('-', $rx_matches);
						}
						$arh[$arh_key] = $val;
					}
				}
				return( $arh );
			}
		}

		$requestHeaders = apache_request_headers();

		$authorizationHeader = isset($requestHeaders['Authorization'])
			? $requestHeaders['Authorization'] : null;

		if ( ! $authorizationHeader) {
			$scope['message'] = "Заголовок авторизации не получен.";
			return Response::json($scope, 401);
		}

		$token = str_replace('Bearer ', '', $authorizationHeader);
		$secret = Config::get('app.key');
		$decoded_token = null;

		try {
			$decoded = JWT::decode($token, $secret);
		} catch(UnexpectedValueException $ex) {
			$scope['message'] = "Недействительный токен.";
			return Response::json($scope, 401);
		}

		$userId = $decoded->user;

		if ( ! $userId) {
			$scope['message'] = "Недействительный токен.";
			return Response::json($scope, 401);
		}

	}

	try {
		$user = \Sentry::findUserById($userId);
	} catch (Cartalyst\Sentry\Users\UserNotFoundException $e) {
		$scope['message'] = "Пользователь не найден.";
		return Response::json($scope, 401);
	}

	LemonTree\LoggedUser::setUser($user);

});

Route::group(array('prefix' => 'admin'), function() {

	Route::get('/', 'LemonTree\HomeController@getIndex');

});

Route::group(array('prefix' => 'api'), function() {

	Route::post('login', 'LemonTree\LoginController@postLogin');

});

Route::group(array(
	'prefix' => 'api',
	'before' => 'admin.auth'
), function() {

	Route::get('user', 'LemonTree\LoginController@getUser');

	Route::post('profile', 'LemonTree\ProfileController@postSave');

	Route::post('group/add', 'LemonTree\GroupController@save');

	Route::get('group/{id}', 'LemonTree\GroupController@getGroup')->
		where('id', '[0-9]+');

	Route::post('group/{id}', 'LemonTree\GroupController@save')->
		where('id', '[0-9]+');

	Route::delete('group/{id}', 'LemonTree\GroupController@delete')->
		where('id', '[0-9]+');

	Route::get('group/{id}/items', 'LemonTree\GroupController@getItemPermissions')->
		where('id', '[0-9]+');

	Route::post('group/{id}/items', 'LemonTree\GroupController@postSaveItemPermissions')->
		where('id', '[0-9]+');

	Route::get('group/{id}/elements', 'LemonTree\GroupController@getElementPermissions')->
		where('id', '[0-9]+');

	Route::post('group/{id}/elements', 'LemonTree\GroupController@postSaveElementPermissions')->
		where('id', '[0-9]+');

	Route::get('group/list', 'LemonTree\GroupController@getList');

	Route::get('user/form', 'LemonTree\UserController@getForm');

	Route::post('user/add', 'LemonTree\UserController@save');

	Route::get('user/{id}', 'LemonTree\UserController@getUser')->
		where('id', '[0-9]+');

	Route::post('user/{id}', 'LemonTree\UserController@save')->
		where('id', '[0-9]+');

	Route::delete('user/{id}', 'LemonTree\UserController@delete')->
		where('id', '[0-9]+');

	Route::get('log', 'LemonTree\LogController@getLog');

	Route::get('log/form', 'LemonTree\LogController@getForm');

	Route::get('user/list', 'LemonTree\UserController@getList');

	Route::get('group/{id}/user/list', 'LemonTree\UserController@getListByGroup')->
		where('id', '[0-9]+');

	Route::get('browse/{classId?}', 'LemonTree\BrowseController@getIndex');

	Route::get('binds/{classId?}', 'LemonTree\BrowseController@getBinds');

	Route::get('plugin/browse/{classId}', 'LemonTree\PluginController@getBrowsePlugin');

	Route::get('favorites', 'LemonTree\FavoritesController@getList');

	Route::post('favorites/{classId}', 'LemonTree\FavoritesController@postToggle');

	Route::get('tree', 'LemonTree\TreeController@show');

	Route::get('hint/{class}', 'LemonTree\HintController@getHint');

	Route::get('element/{classId}', 'LemonTree\EditController@getElement');

});
