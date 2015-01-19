browse.controller('SearchController', function(
	$rootScope, $scope, $http, $state, $stateParams
) {
	var encodeOptions = function(options) {
		var params = [];

		for (var name in options) {
			var value = encodeURIComponent(options[name]);
			params[params.length] = name+':'+value;
		}

		return params.join(';');
	};

	var decodeOptions = function(encoded) {
		var options = [];

		var params = encoded ? encoded.split(';') : [];

		for (var i in params) {
			var param = params[i].split(':');
			var name = param[0];
			var value = decodeURIComponent(param[1]);
			options[name] = value;
		}

		return options;
	};

	var getElementListView = function(className, options) {
		$http({
			method: 'GET',
			url: 'api/search/'+className,
			params: options
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

	var className = $stateParams.class;
	var options = decodeOptions($stateParams.options);

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
					setTimeout(function() {
						$('#items-container').slideDown('fast');
					});
				},
				function(error) {
					console.log(error);
				}
			);
		});
	};

	$scope.selectItem = function(className, options) {
		$('#item-container').slideUp('fast', function() {
			$http({
				method: 'GET',
				url: 'api/search/item/'+className,
				params: options
			}).then(
				function(response) {
					$scope.currentItem = response.data.item;
					$scope.sortProperty = response.data.sortProperty;
					$scope.propertyList = response.data.propertyList;
					setTimeout(function() {
						$('#item-container').slideDown('fast');
					});
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
					setTimeout(function() {
						$('#properties-container').slideDown('fast');
					});
				},
				function(error) {
					console.log(error);
				}
			);
		});
	};

	$scope.search = function() {
		var propertyList = $scope.propertyList;

		var options = {
			action: 'search',
		};

		for (var i in propertyList) {
			var name = propertyList[i].searchView.name;
			var value = propertyList[i].searchView.value;
			var open = propertyList[i].searchView.open;

			if (name && value && open) {
				if (typeof value === 'object') {
					for (var i in value) {
						options[name+'_'+i] = value[i];
					}
				} else {
					options[name] = value;
				}
			}
		}

		options = encodeOptions(options);

		$state.go('base.searchItem', {
			class: $scope.currentItem.name,
			options: options
		});
	};

	$http({
		method: 'GET',
		url: 'api/search/items'
	}).then(
		function(response) {
			$scope.sortItem = response.data.sortItem;
			$scope.itemList = response.data.itemList;
			$scope.empty = $scope.itemList.length ? false : true;
			if (className) {
				$scope.selectItem(className, options);
			} else if (response.data.currentItem) {
				$scope.selectItem(response.data.currentItem.name, options);
			}
		},
		function(error) {
			console.log(error);
		}
	);

	if (className && options) {
		getElementListView(className, options);
	}
});