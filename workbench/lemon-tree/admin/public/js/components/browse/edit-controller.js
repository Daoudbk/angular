browse.controller('EditController', function(
	$rootScope, $scope, $http, $state, $stateParams
) {
	var classId = $stateParams.classId;

	$rootScope.activeIcon = 'edit';

	$scope.currentElement = null;
	$scope.parentElement = null;
	$scope.parentList = [];
	$scope.currentItem = null;
	$scope.propertyList = [];

	$http({
		method: 'GET',
		url: 'api/element/'+classId
	}).then(
		function(response) {
			$scope.currentElement = response.data.currentElement;
			$scope.parentElement = response.data.parentElement;
			$scope.parentList = response.data.parentList;
			$scope.currentItem = response.data.currentItem;
			$scope.propertyList = response.data.propertyList;
		},
		function(error) {
			console.log(error);
		}
	);

	$scope.up = function() {
		if ($scope.parentElement) {
			$state.go('base.browseElement', {classId: $scope.parentElement.classId});
		} else {
			$state.go('base.browse');
		}
	};

	$scope.submit = function() {
		console.log($scope);
	};
});