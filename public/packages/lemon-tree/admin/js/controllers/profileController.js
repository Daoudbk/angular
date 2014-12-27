var profile = angular.module('ProfileCtrl', []);

profile.controller('ProfileController', function(
	$scope, $rootScope, $location
) {
	$scope.submit = function() {
		$('#wrapper').toggleClass('toggled');
	};
});