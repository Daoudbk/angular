<?php namespace LemonTree;

class UserController extends \BaseController {

	public function getUser($id)
	{
		$scope = array();

		try {
			$user = \Sentry::findUserById($id);
		} catch (\Exception $e) {
			$scope['state'] = 'error_user_not_found';
			return \Response::json($scope);
		}

		$loggedUser = LoggedUser::getUser();

		if ( ! $loggedUser->hasAccess('admin')) {
			$scope['state'] = 'error_admin_access_denied';
			return \Response::json($scope);
		}

		if (
			$loggedUser->id == $user->id
			|| $user->isSuperUser()
		) {
			$scope['state'] = 'error_user_access_denied';
			return \Response::json($scope);
		}

		$groupList = Group::orderBy('name', 'asc')->get();

		$userGroups = $user->getGroups();

		$userGroupMap = array();

		foreach ($userGroups as $group) {
			$userGroupMap[$group->id] = true;
		}

		$user->groups = $userGroupMap;
		$user->isSuperUser = $user->isSuperUser();

		$scope['user'] = $user;
		$scope['groupList'] = $groupList;

		return \Response::json($scope);
	}

	public function postSave($id)
	{
		$scope = array();

		try {
			$user = \Sentry::findUserById($id);
		} catch (\Exception $e) {
			$scope['state'] = 'error_user_not_found';
			return \Response::json($scope);
		}

		$loggedUser = LoggedUser::getUser();

		if ( ! $loggedUser->hasAccess('admin')) {
			$scope['state'] = 'error_admin_access_denied';
			return \Response::json($scope);
		}

		if (
			$loggedUser->id == $user->id
			|| $user->isSuperUser()
		) {
			$scope['state'] = 'error_user_access_denied';
			return \Response::json($scope);
		}

		$input = \Input::all();

		$rules = array(
			'login' => 'required',
			'email' => 'required|email',
			'first_name' => 'required',
			'last_name' => 'required',
		);

		$messages = array(
			'login.required' => 'Поле обязательно к заполнению',
			'email.required' => 'Поле обязательно к заполнению',
			'email' => 'Некорректный адрес электронной почты',
			'first_name.required' => 'Поле обязательно к заполнению',
			'last_name.required' => 'Поле обязательно к заполнению',
		);

		$titles = array(
			'login' => 'Логин',
			'email' => 'E-mail',
			'password' => 'Пароль',
			'first_name' => 'Имя',
			'last_name' => 'Фамилия',
		);

		$groups = \Input::get('groups');

		if (is_array($groups)) {
			foreach ($groups as $id => $value) {
				if ($value === true) {
					$input['group_'.$id] = $id;
					$rules['group_'.$id] = 'exists:cytrus_groups,id';
					$messages['group_'.$id.'.exists'] = 'Некорректный идентификатор';
					$titles['group_'.$id] = 'Группа '.$id;
				}
			}
		}

		$validator = \Validator::make($input, $rules, $messages);

		if ($validator->fails()) {
			$messages = $validator->messages()->getMessages();
			$errors = array();
			foreach ($messages as $field => $messageList) {
				foreach ($messageList as $message) {
					$errors[$field][] = array(
						'title' => $titles[$field],
						'message' => $message,
					);
				}
			}
			$scope['error'] = $errors;
			return json_encode($scope);
		}

		$user->login = \Input::get('login');
		if (\Input::get('password')) {
			$user->password = \Input::get('password');
		}
		$user->email = \Input::get('email');
		$user->first_name = \Input::get('first_name');
		$user->last_name = \Input::get('last_name');

		$user->save();

		$userGroups = $user->getGroups();

		$userGroupMap = array();

		foreach ($userGroups as $userGroup) {
			if (
				! isset($groups[$userGroup->id])
				|| $groups[$userGroup->id] !== true
			) {
				$user->removeGroup($userGroup);
			} else {
				$userGroupMap[$userGroup->id] = true;
			}
		}

		foreach ($groups as $id => $value) {
			if (
				$value === true
				&& ! isset($userGroupMap[$id])
			) {
				try {
					$group = \Sentry::findGroupById($id);
					$user->addGroup($group);
					$userGroupMap[$group->id] = true;
				} catch (\Cartalyst\Sentry\Groups\GroupNotFoundException $e) {}
			}
		}

		$user->flush();

		$user->groups = $userGroupMap;

		UserAction::log(
			UserActionType::ACTION_TYPE_SAVE_USER_ID,
			'ID '.$user->id.' ('.$user->login.')'
		);

		$scope['user'] = $user;
		$scope['status'] = 'ok';

		return \Response::json($scope);
	}

	public function getList()
	{
		$scope = array();

		$userList = User::orderBy('login', 'asc')->get();

		foreach ($userList as $user) {
			$user->groups = $user->getGroups();
			$user->isSuperUser = $user->isSuperUser();
		}

		$scope['userList'] = $userList;

		return \Response::json($scope);
	}

	public function getListByGroup($id)
	{
		$scope = array();

		try {
			$activeGroup = \Sentry::findGroupById($id);
		} catch (\Exception $e) {
			$scope['state'] = 'error_group_not_found';
			return \Response::json($scope);
		}

		$loggedUser = LoggedUser::getUser();

		if ( ! $loggedUser->hasAccess('admin')) {
			$scope['state'] = 'error_admin_access_denied';
			return \Response::json($scope);
		}

		if ($loggedUser->inGroup($activeGroup)) {
			$scope['state'] = 'error_group_access_denied';
			return \Response::json($scope);
		}

		$userList = $activeGroup->users()->orderBy('login', 'asc')->get();

		foreach ($userList as $user) {
			$user->groups = $user->getGroups();
			$user->isSuperUser = $user->isSuperUser();
		}

		$scope['userList'] = $userList;

		return \Response::json($scope);
	}

}
