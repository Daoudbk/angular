browse.controller('TrashController', function(
	$rootScope, $scope, $http
) {
	$rootScope.activeIcon = 'trash';

	$scope.categoryList = [];

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