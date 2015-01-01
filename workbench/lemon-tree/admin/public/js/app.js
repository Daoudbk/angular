var app = angular.module('adminApp', [
	'ui.router', 'ui.bootstrap', 'ngAnimate',
	'LoginCtrl',
	'NavbarCtrl', 'BrowseCtrl', 'UsersCtrl',
]);

app.run(function(
	$rootScope, $state, $document,
	AuthToken, Login, Alert
){
	$rootScope.$on('$stateChangeStart', function(
		event, toState, toParams, fromState, fromParams
	){
		$rootScope.currentState = toState;

		if (AuthToken.isAuthenticated()) {
			Login.user(
				function(response) {
					$rootScope.loggedUser = response.data.user;
					$rootScope.$broadcast('loggedUser', response.data.user);
				}
			);
			if (toState.name == 'simple.login') {
				event.preventDefault();
				$state.go('base.browse');
			}
		} else {
			if (toState.name != 'simple.login') {
				event.preventDefault();
				$state.go('simple.login');
			}
		}
	});

	$document
		.on('keypress', function(event){
			return Alert.handleKeys(event);
		})
		.on('keydown', function(event){
			return Alert.handleKeys(event);
		});

	$rootScope.timestamp = function(datetime) {
		return datetime
			? new Date(Date.parse(datetime))
			: null;
	};

	$rootScope.toDateString = function(date) {
		if ( ! date) return null;

		var dateString =
			date.getFullYear()
			+'-'+('0' + (date.getMonth() + 1)).slice(-2)
			+'-'+('0' + date.getDate()).slice(-2);

		return dateString;
	};
});

app.config(function(
	$stateProvider, $urlRouterProvider, $httpProvider
) {
	var baseTemplatePath = 'packages/lemon-tree/admin/js/templates/';

	var templatePath = function(template) {
		return baseTemplatePath+template;
	};

	$httpProvider.interceptors.push('AuthInterceptor');
	$httpProvider.interceptors.push('FormInterceptor');

	$urlRouterProvider.otherwise('/');

	$stateProvider
	.state('simple', {
		templateUrl: templatePath('simple.html')
	})
	.state('base', {
		templateUrl: templatePath('layout.html')
	})
	.state('simple.login', {
		url: '/login',
		templateUrl: templatePath('login.html'),
		controller: 'LoginController'
	})
	.state('base.browse', {
		url: '/',
		templateUrl: templatePath('browse.html'),
		controller: 'BrowseController'
	})
	.state('base.search', {
		url: '/search',
		templateUrl: templatePath('search.html'),
		controller: 'SearchController'
	})
	.state('base.trash', {
		url: '/trash',
		templateUrl: templatePath('browse.html'),
		controller: 'BrowseController'
	})
	.state('base.users', {
		url: '/users',
		templateUrl: templatePath('users.html'),
		controller: 'UsersController'
	})
	.state('base.log', {
		url: '/log?id',
		templateUrl: templatePath('log.html'),
		controller: 'LogController'
	})
	.state('base.group', {
		url: '/group/{id:[0-9]+}',
		templateUrl: templatePath('group.html'),
		controller: 'GroupController'
	})
	.state('base.groupAdd', {
		url: '/group/add',
		templateUrl: templatePath('group.html'),
		controller: 'GroupController'
	})
	.state('base.groupUsers', {
		url: '/group/{id:[0-9]+}/users',
		templateUrl: templatePath('groupUsers.html'),
		controller: 'GroupUsersController'
	})
	.state('base.groupItems', {
		url: '/group/{id:[0-9]+}/items',
		templateUrl: templatePath('groupItems.html'),
		controller: 'ItemPermissionsController'
	})
	.state('base.groupElements', {
		url: '/group/{id:[0-9]+}/elements',
		templateUrl: templatePath('groupElements.html'),
		controller: 'ElementPermissionsController'
	})
	.state('base.user', {
		url: '/user/{id:[0-9]+}',
		templateUrl: templatePath('user.html'),
		controller: 'UserController'
	})
	.state('base.userAdd', {
		url: '/user/add',
		templateUrl: templatePath('user.html'),
		controller: 'UserController'
	})
	.state('base.profile', {
		url: '/profile',
		templateUrl: templatePath('profile.html'),
		controller: 'ProfileController'
	});

	$.blockUI.defaults.message = '<img src="packages/lemon-tree/admin/img/loader.gif" />';
	$.blockUI.defaults.css.border = 'none';
	$.blockUI.defaults.css.background = 'none';
	$.blockUI.defaults.overlayCSS.opacity = 0.2;
	$.blockUI.defaults.fadeIn = 50;

});

app.directive('submitOn', function() {
    return function(scope, element, attrs) {
		element.attr('onsubmit', 'return false');
		scope.$on(attrs.submitOn, function() {
			setTimeout(function() {
				element.trigger('submit');
			});
		});
    };
});

app.directive('ctrlLeft', function() {
    return function(scope, element, attrs) {
		scope.$on(attrs.ctrlLeft, function() {
			scope.prev();
		});
    };
});

app.directive('ctrlRight', function() {
    return function(scope, element, attrs) {
		scope.$on(attrs.ctrlRight, function() {
			scope.next();
		});
    };
});

app.factory('AuthToken', function($window) {
	var tokenKey = 'token';

	return {
		isAuthenticated: isAuthenticated,
		setToken: setToken,
		getToken: getToken,
		clearToken: clearToken
	};

	function setToken(token) {
		$window.localStorage.setItem(tokenKey, token);
	}

	function getToken() {
		return $window.localStorage.getItem(tokenKey);
	}

	function clearToken() {
		$window.localStorage.removeItem(tokenKey);
	}

	function isAuthenticated() {
		return !! getToken();
	}
});

app.factory('AuthInterceptor', function ($q, $injector, AuthToken) {
	return {
		request: function (config) {
			var token = AuthToken.getToken();

			if (token) {
				config.headers = config.headers || {};
				config.headers.Authorization = 'Bearer ' + token;
			}

			return config;
		},
		response: function (response) {
			return response;
		},
		responseError: function(rejection) {
			var state = $injector.get('$state');

			if (rejection.status === 401) {
				state.go('simple.login');
			}

			return $q.reject(rejection);
		}
	};
});

app.factory('FormInterceptor', function ($q, $rootScope, $injector, Alert) {
	return {
		request: function (config) {
			if (config.checkForm) {
				Alert.onSubmit();
			}

			return config;
		},
		response: function (response) {
			var state = $injector.get('$state');

			if (response.data.state === 'error_admin_access_denied') {
				state.go('base.browse');
			} else if (response.data.state === 'error_group_not_found') {
				state.go('base.users');
			} else if (response.data.state === 'error_group_access_denied') {
				state.go('base.users');
			} else if (response.data.state === 'error_user_not_found') {
				state.go('base.users');
			} else if (response.data.state === 'error_user_access_denied') {
				state.go('base.users');
			} else if (response.data.message) {
				$rootScope.message = response.data.message;
			} else if (response.config.checkForm) {
				Alert.onResponse(response);
			}

			return response;
		},
		responseError: function(rejection) {
			return $q.reject(rejection);
		}
	};
});

app.factory('Alert', function($rootScope) {
	return {
		handleKeys: function(event) {
			var code = event.keyCode || event.which;

			if (code == 83 && event.ctrlKey == true) {
				$rootScope.$broadcast('CtrlS');
				return false;
			}

			if (code == 37 && event.ctrlKey == true) {
				$rootScope.$broadcast('CtrlLeft');
				return false;
			}

			if (code == 39 && event.ctrlKey == true) {
				$rootScope.$broadcast('CtrlRight');
				return false;
			}

			return true;
		},
		onSubmit: function() {
			$('[data-toggle="popover"]')
				.attr('data-content', '')
				.focus(function() {
					$(this).popover('hide');
					$(this).parent().removeClass('has-error');
				})
				.popover({
					placement: 'left',
					trigger: 'manual',
				})
				.popover('hide');

			$('[data-toggle="popover"]').parent()
				.removeClass('has-error');

			$('#modal').on('hidden.bs.modal', function (e) {
				$('[data-toggle="popover"]').popover('show');
			});

			$.blockUI();
		},
		onResponse: function(response) {
			if (errors = response.data.error) {
				var html = '';

				for (var propertyName in errors) {
					var propertyHtml = '';

					for (var i in errors[propertyName]) {
						var title = errors[propertyName][i].title;
						var message = errors[propertyName][i].message;
						if (title && message) {
							propertyHtml += message+' ';
							html += '<strong>'+title+'.</strong> '+message+'<br />';
						}
					}

					$('[id="'+propertyName+'"]').parent().addClass('has-error');
					$('[id="'+propertyName+'"]').attr('data-content', propertyHtml);
				}

				if (html) {
					$('.modal-body').html(html);
					$('#modal').modal();
				}
			}
			$.unblockUI();
		},
	};
});

var login = angular.module('LoginCtrl', []);

login.factory('Login', function(
	$http
){
	return {
		login: function(credentials, onSuccess, onFailed) {
			$http({
				method: 'POST',
				url: 'api/login',
				data: credentials,
			}).then(
				onSuccess,
				onFailed
			);
		},
		user: function(onSuccess, onFailed) {
			$http({
				method: 'GET',
				url: 'api/user'
			}).then(
				onSuccess,
				onFailed
			);
		}
	};
});

login.controller('LoginController', function(
	$rootScope, $scope, $state,
	Login, AuthToken
) {
	$scope.message = null;

	$scope.submit = function() {
		Login.login(
			$scope.loginData,
			function(response) {
				AuthToken.setToken(response.data.token);
				$rootScope.loggedUser = response.data.user;
				$state.go('base.browse');
			},
			function(error) {
				$scope.message = error.data.message;
			}
		);
	};
});

var navbar = angular.module('NavbarCtrl', []);

navbar.controller('NavbarController', function(
	$rootScope, $scope, $state,
	AuthToken
) {
	$scope.toggle = function() {
		$('#toggle-button').blur();
		$('#wrapper').toggleClass('toggled');
	};

	$scope.home = function() {
		$state.go('base.browse');
	};

	$scope.refresh = function() {
		$state.reload();
	};

	$scope.search = function() {
		$state.go('base.search');
	};

	$scope.trash = function() {
		$state.go('base.trash');
	};

	$scope.users = function() {
		$state.go('base.users');
	};

	$scope.profile = function() {
		$state.go('base.profile');
	};

	$scope.logout = function() {
		AuthToken.clearToken();
		$rootScope.loggedUser = null;
		$state.go('simple.login');
	};
});

var browse = angular.module('BrowseCtrl', []);

browse.controller('BrowseController', function(
	$rootScope, $scope, $http
) {
	$rootScope.activeIcon = 'browse';

	$scope.categoryList = null;

	$http({
		method: 'GET',
		url: 'api/browse'
	}).then(
		function(response) {
			$scope.categoryList = response.data.categoryList;
		},
		function(error) {
			console.log(error);
		}
	);
});

var users = angular.module('UsersCtrl', []);

users.controller('ProfileController', function(
	$rootScope, $scope, $http
) {
	$rootScope.activeIcon = 'profile';

	$scope.$on('loggedUser', function (event, loggedUser) {
		$scope.profile = loggedUser;
	});

	$scope.submit = function() {
		$http({
			method: 'POST',
			url: 'api/profile/save',
			data: $scope.profile,
			checkForm: true,
		});
	};
});

users.controller('UsersController', function(
	$rootScope, $scope, $http
) {
	var groupList = function() {
		$http({
			method: 'GET',
			url: 'api/group/list'
		}).then(
			function(response) {
				$scope.groupList = response.data.groupList;
			},
			function(error) {
				console.log(error);
			}
		);
	};

	var userList = function() {
		$http({
			method: 'GET',
			url: 'api/user/list'
		}).then(
			function(response) {
				$scope.userList = response.data.userList;
			},
			function(error) {
				console.log(error);
			}
		);
	};

	$rootScope.activeIcon = 'users';

	$scope.groupList = [];
	$scope.userList = [];

	groupList();
	userList();

	$scope.deleteGroup = function(id) {
		$.blockUI();
		$http({
			method: 'DELETE',
			url: 'api/group/'+id,
		}).then(
			function(response) {
				groupList();
				userList();
				$.unblockUI();
			},
			function(error) {
				console.log(error);
			}
		);
	};

	$scope.deleteUser = function(id) {
		$.blockUI();
		$http({
			method: 'DELETE',
			url: 'api/user/'+id,
		}).then(
			function(response) {
				userList();
				$.unblockUI();
			},
			function(error) {
				console.log(error);
			}
		);
	};
});

users.controller('GroupController', function(
	$rootScope, $scope, $http, $state, $stateParams
) {
	var id = $stateParams.id;

	$rootScope.activeIcon = 'users';

	$scope.id = id;
	$scope.group = null;

	if (id) {
		$http({
			method: 'GET',
			url: 'api/group/'+id
		}).then(
			function(response) {
				$scope.group = response.data.group;
			},
			function(error) {
				console.log(error);
			}
		);
	}

	$scope.submit = function() {
		$http({
			method: 'POST',
			url: (id ? 'api/group/'+id : 'api/group/add'),
			data: $scope.group,
			checkForm: true,
		}).then(
			function(response) {
				if (response.data.group) {
					if (id) {
						$scope.group = response.data.group;
					} else {
						$state.go('base.users');
					}
				}
			},
			function(error) {
				console.log(error);
			}
		);
	};
});

users.controller('GroupUsersController', function(
	$rootScope, $scope, $http, $stateParams
) {
	var id = $stateParams.id;

	$rootScope.activeIcon = 'users';

	$scope.group = null;
	$scope.userList = [];

	$http({
		method: 'GET',
		url: 'api/group/'+id+'/user/list',
	}).then(
		function(response) {
			$scope.group = response.data.group;
			$scope.userList = response.data.userList;
		},
		function(error) {
			console.log(error);
		}
	);
});

users.controller('ItemPermissionsController', function(
	$rootScope, $scope, $http, $stateParams
) {
	var id = $stateParams.id;

	$rootScope.activeIcon = 'users';

	$scope.group = null;
	$scope.itemList = [];
	$scope.permission = {};

	$http({
		method: 'GET',
		url: 'api/group/'+id+'/items'
	}).then(
		function(response) {
			var defaultPermission = response.data.defaultPermission;
			var permissionList = response.data.permissionList;

			if (response.data.group) {
				$scope.group = response.data.group;
			}

			if (response.data.itemList) {
				$scope.itemList = response.data.itemList;
			}

			for (var name in $scope.itemList) {
				$scope.permission[name] =
					permissionList[name] || defaultPermission;
			}
		},
		function(error) {
			console.log(error);
		}
	);

	$scope.submit = function() {
		$http({
			method: 'POST',
			url: 'api/group/'+id+'/items',
			data: $scope.permission,
			checkForm: true,
		}).then(
			function(response) {

			},
			function(error) {
				console.log(error);
			}
		);
	};
});

users.controller('ElementPermissionsController', function(
	$rootScope, $scope, $http, $stateParams
) {
	var id = $stateParams.id;

	$rootScope.activeIcon = 'users';

	$scope.group = null;
	$scope.itemList = [];
	$scope.itemElementList = [];
	$scope.permission = {};

	$http({
		method: 'GET',
		url: 'api/group/'+id+'/elements'
	}).then(
		function(response) {
			var defaultPermission = response.data.defaultPermission;
			var permissionList = response.data.permissionList;

			$scope.group = response.data.group;
			$scope.itemList = response.data.itemList;
			$scope.itemElementList = response.data.itemElementList;

			for (var itemName in $scope.itemElementList) {
				for (var classId in $scope.itemElementList[itemName]) {
					$scope.permission[classId] =
						permissionList[classId]
						|| permissionList[itemName]
						|| defaultPermission;
				}
			}
		},
		function(error) {
			console.log(error);
		}
	);

	$scope.submit = function() {
		$http({
			method: 'POST',
			url: 'api/group/'+id+'/elements',
			data: $scope.permission,
			checkForm: true,
		}).then(
			function(response) {

			},
			function(error) {
				console.log(error);
			}
		);
	};
});

users.controller('UserController', function(
	$rootScope, $scope, $http, $state, $stateParams
) {
	var id = $stateParams.id;

	$rootScope.activeIcon = 'users';

	$scope.id = id;
	$scope.user = null;
	$scope.groupList = [];

	$http({
		method: 'GET',
		url: 'api/user/form'
	}).then(
		function(response) {
			$scope.groupList = response.data.groupList;
		},
		function(error) {
			console.log(error);
		}
	);

	if (id) {
		$http({
			method: 'GET',
			url: 'api/user/'+id
		}).then(
			function(response) {
				$scope.user = response.data.user;
			},
			function(error) {
				console.log(error);
			}
		);
	}

	$scope.submit = function() {
		$http({
			method: 'POST',
			url: (id ? 'api/user/'+id : 'api/user/add'),
			data: $scope.user,
			checkForm: true,
		}).then(
			function(response) {
				if (response.data.user) {
					if (id) {
						$scope.user = response.data.user;
					} else {
						$state.go('base.users');
					}
				}
			},
			function(error) {
				console.log(error);
			}
		);
	};
});

users.controller('LogController', function(
	$rootScope, $scope, $http, $stateParams, $window
) {
	var id = $stateParams.id;
	var currentPage = $window.localStorage.getItem('log_current_page') || 1;
	var blocked = false;

	var getForm = function() {
		$http({
			method: 'GET',
			url: 'api/log/form',
			params: {
				user: id,
			}
		}).then(
			function(response) {
				$scope.activeUser = response.data.activeUser;
				$scope.userList = response.data.userList;
				$scope.actionTypeList = response.data.actionTypeList;

				getList();
			},
			function(error) {
				console.log(error);
			}
		);
	};

	var getList = function(stop) {
		blocked = true;

		var userId = $scope.activeUser
			? $scope.activeUser.id
			: null;

		var dateFromString = $scope.filter.dateFrom
			? $rootScope.toDateString($scope.filter.dateFrom)
			: null;

		var dateToString = $scope.filter.dateTo
			? $rootScope.toDateString($scope.filter.dateTo)
			: null;

		$http({
			method: 'GET',
			url: 'api/log',
			params: {
				user: userId,
				actionType: $scope.filter.actionType,
				comments: $scope.filter.comments,
				dateFrom: dateFromString,
				dateTo: dateToString,
				page: currentPage,
				perPage: $scope.perPage,
			}
		}).then(
			function(response) {
				$scope.userActionList = response.data.userActionList;
				$scope.count = response.data.count;
				$scope.currentPage = response.data.currentPage;

				if (
					currentPage !== response.data.currentPage
					&& ! stop
				) {
					currentPage = response.data.currentPage;
					getList(true);
					return false;
				}

				$window.localStorage.setItem('log_current_page', currentPage);

				$scope.empty = $scope.userActionList.length ? false : true;

				blocked = false;

				$.unblockUI();
			},
			function(error) {
				console.log(error);
			}
		);
	};

	$rootScope.activeIcon = 'users';

	$scope.activeUser = null;
	$scope.userList = [];
	$scope.actionTypeList = [];
	$scope.userActionList = [];
	$scope.empty = false;

	$scope.dateFromOpened = false;
	$scope.dateToOpened = false;

	$scope.filter = {
		actionType: null,
		comments: null,
		dateFrom:  null,
		dateTo:  null,
	};

	$scope.perPage = 10;

	getForm();

	$scope.submit = function() {
		$.blockUI();
		currentPage = 1;
		getList();
	};

	$scope.pageChanged = function() {
		$.blockUI();
		currentPage = $scope.currentPage;
		getList();
	};

	$scope.prev = function() {
		if ( ! blocked && $scope.currentPage > 1) {
			$scope.currentPage--;
			$scope.pageChanged();
		}
	};

	$scope.next = function() {
		if ( ! blocked && $scope.currentPage < $scope.numPages) {
			$scope.currentPage++;
			$scope.pageChanged();
		}
	};

	$scope.openDateFrom = function($event) {
		$event.preventDefault();
		$event.stopPropagation();

		$scope.dateFromOpened = true;
		$scope.dateToOpened = false;
	 };

	 $scope.openDateTo = function($event) {
		$event.preventDefault();
		$event.stopPropagation();

		$scope.dateToOpened = true;
		$scope.dateFromOpened = false;
	 };

});


