;(function($) {
	$('#btn-tyk-api-subscribe').click(function(e) {
		e.preventDefault();
		var data = { 
			action: 'get_token',
			api: $('#tyk-api-select').val(),
			token_name: $('#tyk-token-name').val()
		};
		$.post(scriptParams.actionUrl, data)
			.done(function(result) {
				if (result && result.success) {
					$('#tyk-subscribe-success')
						.removeClass('hidden')
						.html(result.data.message);
				}
				else {
					$('#tyk-subscribe-error')
						.removeClass('hidden')
						.html(scriptParams.generalErrorMessage);
					console.error(result);
				}
			});
	});
})(jQuery);