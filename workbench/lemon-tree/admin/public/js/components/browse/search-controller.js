browse.controller('SearchController', function(
	$rootScope, $scope, $http, $stateParams
) {
	var className = $stateParams.class;

	var getElementListView = function(className) {
		$http({
			method: 'GET',
			url: 'api/search/'+className
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

	$rootScope.activeIcon = 'search';

	$scope.itemList = [];
	$scope.propertyList = [];
	$scope.currentItem = null;
	$scope.sortItem = null;
	$scope.sortProperty = null;
	$scope.elementListView = null;
	$scope.empty = false;

	$scope.sortItems = function(sort) {
		$('#items-container').slideUp('fast', function() {
			$http({
				method: 'GET',
				url: 'api/search/items/',
				params: {sort: sort}
			}).then(
				function(response) {
					$scope.sortItem = response.data.sortItem;
					$scope.itemList = response.data.itemList;
					$('#items-container').slideDown('fast');
				},
				function(error) {
					console.log(error);
				}
			);
		});
	};

	$scope.selectItem = function(item) {
		$('#item-container').slideUp('fast', function() {
			$http({
				method: 'GET',
				url: 'api/search/item/'+item.name
			}).then(
				function(response) {
					$scope.currentItem = response.data.item;
					$scope.sortProperty = response.data.sortProperty;
					$scope.propertyList = response.data.propertyList;
					$('#item-container').slideDown('fast');
				},
				function(error) {
					console.log(error);
				}
			);
		});
	};

	$scope.sortProperties = function(sort) {
		if ( ! $scope.currentItem) return false;

		$('#properties-container').slideUp('fast', function() {
			$http({
				method: 'GET',
				url: 'api/search/item/'+$scope.currentItem.name,
				params: {sort: sort}
			}).then(
				function(response) {
					$scope.sortProperty = response.data.sortProperty;
					$scope.propertyList = response.data.propertyList;
					$('#properties-container').slideDown('fast');
				},
				function(error) {
					console.log(error);
				}
			);
		});
	};

	$scope.search = function() {
		console.log($scope);
	};

	$http({
		method: 'GET',
		url: 'api/search/items'
	}).then(
		function(response) {
			$scope.sortItem = response.data.sortItem;
			$scope.itemList = response.data.itemList;
			$scope.empty = $scope.itemList.length ? false : true;
			if (response.data.currentItem) {
				$scope.selectItem(response.data.currentItem);
			}
		},
		function(error) {
			console.log(error);
		}
	);
});