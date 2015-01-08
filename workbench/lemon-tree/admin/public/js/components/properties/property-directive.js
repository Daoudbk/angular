property.directive('property', function ($http, Helper) {
	return {
		restrict: "E",
		replace: true,
		scope: {
			type: "=",
			mode: "=",
			view: "=",
		},
		template: '<ng-include src="getTemplateUrl()"></ng-include>',
		link: function(scope, element, attrs) {
			scope.Helper = Helper;

			scope.getTemplateUrl = function() {
				return Helper.templatePath(
					'components/properties/'+scope.type+'/'+attrs.mode
				);
			};

			scope.getList = function(viewValue) {
				var params = {term: viewValue};
				return $http.get('api/hint/'+scope.view.relatedClass, {params: params})
					.then(function(response) {
						return response.data;
					});
			};
		}
	};
});