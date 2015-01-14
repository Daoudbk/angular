browse.controller('TrashController', function(
	$rootScope, $scope, $http, $stateParams
) {
	var className = $stateParams.class;

	var getItem = function(className) {
		$http({
			method: 'GET',
			url: 'api/trash/item/'+className
		}).then(
			function(response) {
				$scope.currentItem = response.data.item;
				getItems();
			},
			function(error) {
				console.log(error);
			}
		);
	}

	var getItems = function() {
		$http({
			method: 'GET',
			url: 'api/trash/items'
		}).then(
			function(response) {
				$scope.itemList = response.data.itemList;
				$scope.empty = $scope.itemList.length ? false : true;
				if (className) {
					getElementListView(className);
				}
			},
			function(error) {
				console.log(error);
			}
		);
	};

	var getElementListView = function(className) {
		$http({
			method: 'GET',
			url: 'api/trash/'+className
		}).then(
			function(response) {
				if (response.data.elementListView) {
					$scope.elementListView = response.data.elementListView;
				}
			},
			function(error) {
				console.log(error);
			}
		);
	};

	$rootScope.activeIcon = 'trash';

	$scope.itemList = [];
	$scope.currentItem = null;
	$scope.elementListView = null;
	$scope.empty = false;

	if (className) {
		getItem(className);
	} else {
		getItems();
	}
});