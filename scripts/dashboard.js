;(function($) {
	$(document).ready(function() {

		/**
		 * List of user tokens
		 */
		var UserTokenList = new Vue({
			el: '#tyk-token-list',
			data: {
				tokens: []
			},
			beforeCompile: function() {
				this.fetchTokens();
			},
			methods: {
				fetchTokens: function() {
					var self = this;
					$.getJSON(scriptParams.actionUrl, {action: 'get_tokens'}).done(function(result) {
						if (typeof(result) == 'object' && result.data) {
							self.tokens = result.data;
						}
					});
				}	
			}
		});
	});


	$('#btn-tyk-api-subscribe').click(function(e) {
		e.preventDefault();
		var data = { 
			action: 'get_token',
			api: $('#tyk-api-select').val(),
			token_name: $('#tyk-token-name').val()
		};
		// post to server
		$.post(scriptParams.actionUrl, data)
			.done(function(result) {
				// show token on success
				if (result && result.success) {
					$('#tyk-subscribe-success')
						.removeClass('hidden')
						.html(result.data.message);
				}
				// show an error if it failed
				else {
					$('#tyk-subscribe-error')
						.removeClass('hidden')
						.html(scriptParams.generalErrorMessage);
					console.error(result);
				}
			});
	});
})(jQuery);