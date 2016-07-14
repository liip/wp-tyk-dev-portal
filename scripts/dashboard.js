;(function($) {
	$('#btn-tyk-api-subscribe').click(function(e) {
		e.preventDefault();
		var data = { 
			action: 'get_token',
			api: $('#tyk-api-select').val()
		};
		$.post(scriptParams.actionUrl, data)
			.done(function(result) {
				if (result && result.success) {
					$('#tyk-subscribe-success').html(result.data.message);
				}
				else {
					$('#tyk-subscribe-error').html(scriptParams.generalErrorMessage);
					console.error(result);
				}
			});
	});
})(jQuery);