;(function($, Vue) {
	/**
	 * Request token form component
	 */
	var RequestTokenForm = Vue.extend({
		props: ['apis'],
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
			 * Request a token
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
			},

			/**
			 * Reset the message
			 */
			closeMessage: function() {
				this.message = '';
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
			tokensByApi: {},
			message: '',
			hasError: false,
			loading: false,
			availableApis: []
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
			var self = this;
			this.loading = true;

			// we're loading until all requests are done
			$.when( this.fetchApis() ).then(function() {
				self.fetchTokens().done(function() {
					// group tokens by api
					$.each(self.tokens, function() {
						if (!$.isArray(self.tokensByApi[this.api_id])) {
							self.tokensByApi[this.api_id] = [];
						}
						self.tokensByApi[this.api_id].push(this);
					});
					self.loading = false;
				});
			});
		},
		methods: {
			/**
			 * Fetch tokens from server
			 * @return {object} jQuery Deferred
			 */
			fetchTokens: function() {
				var self = this;
				return $.getJSON(scriptParams.actionUrl, {action: 'get_tokens'}).done(function(result) {
					if (typeof(result) == 'object' && result.data && !$.isEmptyObject(result.data)) {
						self.tokens = result.data;
					}
					else {
						// reset tokens in case it was already set
						self.tokens = null;
					}
				});
			},

			/**
			 * Fetch available apis from server
			 * @return {object} jQuery Deferred
			 */
			fetchApis: function() {
				var self = this;
				return $.getJSON(scriptParams.actionUrl, {action: 'get_available_apis'}).done(function(result) {
					if (typeof(result) == 'object' && result.data && !$.isEmptyObject(result.data)) {
						self.availableApis = result.data;
					}
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