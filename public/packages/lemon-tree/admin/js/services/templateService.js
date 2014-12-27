var template = angular.module('TemplateSrvc', []);

template.factory('Template', function(){
	var baseTemplatePath = 'packages/lemon-tree/admin/js/templates/';

	return {
		path: function(template) {
			return baseTemplatePath+template;
		}
	};
});
