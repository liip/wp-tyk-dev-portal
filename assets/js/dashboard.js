;(function($, Vue) {
	/**
	 * Request token form component
	 */
	var RequestTokenForm = Vue.extend({
		data: function() {
			return {
				token_name: '',
				api: '',
				message: '',
				hasError: false,
				inProgress: false
			};
		},
		computed: {
			/**
			 * Check if register button should be shown
			 * @return {boolean}
			 */
			formValid: function() {
				return (this.token_name != '' && this.api != '');
			}
		},
		methods: {
			/**
			 * Register for a token
			 */
			register: function() {
				this.inProgress = true;
				var self = this;

				var data = { 
					action: 'get_token',
					api: this.api,
					token_name: this.token_name
				};

				// post to server
				$.post(scriptParams.actionUrl, data)
					.done(function(result) {
						if (result && result.success) {
							self.message = result.data.message;
							self.$dispatch('new-token');
						}
						else {
							self.hasError = true;
							if (console && console.error) {
								console.error(result);
							}
						}
						self.inProgress = false;
					});
			}
		}
	});


	/**
	 * List of user tokens component
	 */
	var Dashboard = new Vue({
		el: '#tyk-dashboard',
		components: {
			'request-token-form': RequestTokenForm
		},
		data: {
			tokens: null,
			message: '',
			hasError: false,
			loading: false
		},
		events: {
			/**
			 * Request token form got a new token: refresh token list
			 */
			'new-token': function() {
				this.fetchTokens();
			}
		},
		beforeCompile: function() {
			this.fetchTokens();
		},
		methods: {
			/**
			 * Fetch tokens from server
			 */
			fetchTokens: function() {
				var self = this;
				this.loading = true;
				$.getJSON(scriptParams.actionUrl, {action: 'get_tokens'}).done(function(result) {
					if (typeof(result) == 'object' && result.data && !$.isEmptyObject(result.data)) {
						self.tokens = result.data;
					}
					// reset tokens in case it was already set
					else {
						self.tokens = null;
					}
					self.loading = false;
				});
			},

			/**
			 * Revoke a token on tyk api
			 * @param {string} token hash
			 */
			revokeToken: function(hash) {
				var data = {
					action: 'revoke_token',
					token: hash
				};
				var self = this;
				$.post(scriptParams.actionUrl, data)
					.done(function(result) {
						if (result && result.success) {
							console.log(result);
							self.message = result.data.message;
							self.fetchTokens();
						}
						else {
							self.hasError = true;
							if (console && console.error) {
								console.error(result);
							}
						}
					});
			}	
		}
	});
})(jQuery, Vue);