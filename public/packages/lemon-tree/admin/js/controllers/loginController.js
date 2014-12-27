var login = angular.module('LoginCtrl', []);

login.controller('LoginController', function(
	$scope, $rootScope, $location, Login
) {
	$scope.submit = function() {
		var auth = Login.auth($scope.loginData);
		auth.success(function(response) {
			if (response.id) {
				$location.path('/browse');
			} else {
				alert('could not verify your login');
			}
		});
	};
});