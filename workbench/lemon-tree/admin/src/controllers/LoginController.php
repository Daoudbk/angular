<?php namespace LemonTree;

class LoginController extends BaseController {

	public function getUser()
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		$scope['user'] = $loggedUser;

		return \Response::json($scope);
	}

	public function postLogin()
	{
		$scope = array();

		try {

			$credentials = \Input::only('login', 'password');

			$user = \Sentry::authenticate($credentials, false);

			LoggedUser::setUser($user);

			UserAction::log(
				UserActionType::ACTION_TYPE_LOGIN_ID,
				$user->login
			);

		} catch (\Cartalyst\Sentry\Users\LoginRequiredException $e) {
			$scope['message'] = 'Введите логин.';
		} catch (\Cartalyst\Sentry\Users\PasswordRequiredException $e) {
			$scope['message'] = 'Введите пароль.';
		} catch (\Cartalyst\Sentry\Users\WrongPasswordException $e) {
			$scope['message'] = 'Неправильный логин или пароль.';
		} catch (\Cartalyst\Sentry\Users\UserNotFoundException $e) {
			$scope['message'] = 'Неправильный логин или пароль.';
		} catch (\Cartalyst\Sentry\Users\UserNotActivatedException $e) {
			$scope['message'] = 'Пользователь не активирован.';
		} catch (\Cartalyst\Sentry\Throttling\UserSuspendedException $e) {
			$scope['message'] = 'Пользователь временно отключен.';
		} catch (\Cartalyst\Sentry\Throttling\UserBannedException $e) {
			$scope['message'] = 'Пользователь заблокирован.';
		}

		if (isset($scope['message'])) {
			return \Response::json($scope, 401);
		}

		$secret = \Config::get('app.key');

		$payload = array(
            'user' => $user->id,
        );

		$scope['user'] = $user;
		$scope['token'] = \JWT::encode($payload, $secret);

		return \Response::json($scope);
	}

}