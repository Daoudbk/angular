var login = angular.module('AuthSrvc', []);

login.factory('Login', function($http){
	return {
		auth: function(credentials) {
			return $http({
				method: 'POST',
				url: 'api/login/auth',
				params: credentials
			});
		},
		check: function() {
			return $http({
				method: 'GET',
				url: 'api/login/check'
			});
		},
		logout: function() {
			return $http({
				method: 'GET',
				url: 'api/login/destroy'
			});
		},
	};
});
