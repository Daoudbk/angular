alert.factory('Alert', function($rootScope) {
	return {
		handleKeys: function(event) {
			var code = event.keyCode || event.which;

			if (code == 83 && event.ctrlKey == true) {
				$rootScope.$broadcast('CtrlS');
				return false;
			}

			return true;
		},
		onSubmit: function() {
			$('[data-toggle="popover"]')
				.attr('data-content', '')
				.focus(function() {
					$(this).popover('hide');
					$(this).parent().removeClass('has-error');
				})
				.popover({
					placement: 'bottom',
					trigger: 'manual',
				})
				.popover('hide');

			$('[data-toggle="popover"]').parent()
				.removeClass('has-error');

			$('#modal').on('hidden.bs.modal', function (e) {
				$('[data-toggle="popover"]').popover('show');
			});

			$.blockUI();
		},
		onResponse: function(response) {
			if (errors = response.data.error) {
				var html = '';

				for (var propertyName in errors) {
					var propertyHtml = '';

					for (var i in errors[propertyName]) {
						var title = errors[propertyName][i].title;
						var message = errors[propertyName][i].message;
						if (title && message) {
							propertyHtml += message+' ';
							html += '<strong>'+title+'.</strong> '+message+'<br />';
						}
					}

					$('[id="'+propertyName+'"]').parent().addClass('has-error');
					$('[id="'+propertyName+'"]').attr('data-content', propertyHtml);
				}

				if (html) {
					$('.modal-body').html(html);
					$('#modal').modal();
				}
			}
			$.unblockUI();
		},
	};
});