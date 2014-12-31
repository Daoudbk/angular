<?php namespace LemonTree;

use Carbon\Carbon;

class LogController extends \BaseController {

	const DEFAULT_PER_PAGE = 10;

	public function getform()
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		if ( ! $loggedUser->hasAccess('admin')) {
			$scope['state'] = 'error_admin_access_denied';
			return \Response::json($scope);
		}

		$userId = \Input::get('user');

		$activeUser = null;

		if ($userId) {
			try {
				$activeUser = \Sentry::findUserById($userId);
			} catch (\Exception $e) {}
		}

		$userList = User::orderBy('login')->get();

		$userActionTypeList = UserActionType::getActionTypeNameList();

		$actionTypeList = array();

		foreach ($userActionTypeList as $name => $title) {
			$actionTypeList[] = [
				'name' => $name,
				'title' => $title,
			];
		}

		$scope['activeUser'] = $activeUser;
		$scope['userList'] = $userList;
		$scope['actionTypeList'] = $actionTypeList;

		return \Response::json($scope);
	}

	public function getLog()
	{
		$scope = array();

		$loggedUser = LoggedUser::getUser();

		if ( ! $loggedUser->hasAccess('admin')) {
			$scope['state'] = 'error_admin_access_denied';
			return \Response::json($scope);
		}

		$userId = \Input::get('user');
		$actionType = \Input::get('actionType');
		$comments = \Input::get('comments');
		$dateFrom = \Input::get('dateFrom');
		$dateTo = \Input::get('dateTo');
		$perPage = \Input::get('perPage') ?: self::DEFAULT_PER_PAGE;

		if ($actionType && ! UserActionType::actionTypeExists($actionType)) {
			$actionType = null;
		}

		if ($dateFrom) {
			try {
				$dateFrom = Carbon::createFromFormat('Y-m-d', $dateFrom);
			} catch (\Exception $e) {
				$dateFrom = null;
			}
		}

		if ($dateTo) {
			try {
				$dateTo = Carbon::createFromFormat('Y-m-d', $dateTo);
			} catch (\Exception $e) {
				$dateTo = null;
			}
		}

		$userActionListCriteria = UserAction::where(
			function($query) use (
				$userId, $actionType, $comments, $dateFrom, $dateTo
			) {
				if ($userId) {
					$query->where('user_id', $userId);
				}

				if ($actionType) {
					$query->where('action_type', $actionType);
				}

				if ($comments) {
					$query->where('comments', 'ilike', "%$comments%");
				}

				if ($dateFrom) {
					$query->where('created_at', '>=', $dateFrom->format('Y-m-d'));
				}

				if ($dateTo) {
					$query->where('created_at', '<', $dateTo->format('Y-m-d'));
				}
			}
		);

		$userActionListCriteria->
		orderBy('created_at', 'desc')->
		cacheTags('UserAction')->
		rememberForever();

		$userActionList = $userActionListCriteria->paginate($perPage);

		$userActions = array();

		foreach ($userActionList as $userAction) {
			$actionTypeName = UserActionType::getActionTypeName(
				$userAction->action_type
			);
			$userActions[] = [
				'user' => [
					'login' => $userAction->user->login,
					'first_name' => $userAction->user->first_name,
					'last_name' => $userAction->user->last_name,
				],
				'action_type' => $userAction->action_type,
				'action_type_name' => $actionTypeName,
				'url' => $userAction->url,
				'comments' => $userAction->comments,
				'created_at' => $userAction->created_at->format('Y-m-d H:i:s'),
			];
		}

		$count = $userActionList->getTotal();
		$currentPage = $userActionList->getCurrentPage();

		$scope['userActionList'] = $userActions;
		$scope['count'] = $count;
		$scope['currentPage'] = $currentPage;

		return \Response::json($scope);
	}

}
