;(function($, Vue) {
	/**
	 * Register for API token component
	 */
	var RegisterForToken = new Vue({
		el: '#tyk-request-token',
		data: {
			token_name: '',
			api: '',
			message: '',
			hasError: false,
			inProgress: false
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
			 * @param {object} MouseEvent
			 */
			register: function(e) {
				e.preventDefault();
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
							self.$emit('registered');
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
	var UserTokenList = new Vue({
		registerWidget: RegisterForToken,
		el: '#tyk-token-list',
		data: {
			tokens: null,
			message: '',
			hasError: false
		},
		beforeCompile: function() {
			self = this;
			this.fetchTokens();
			// is this the proper way to do this?
			this.$options.registerWidget.$on('registered', function() {
				self.fetchTokens();
			});
		},
		methods: {
			/**
			 * Fetch tokens from server
			 */
			fetchTokens: function() {
				var self = this;
				$.getJSON(scriptParams.actionUrl, {action: 'get_tokens'}).done(function(result) {
					if (typeof(result) == 'object' && result.data && !$.isEmptyObject(result.data)) {
						self.tokens = result.data;
					}
					// reset tokens in case it was already set
					else {
						self.tokens = null;
					}
				});
			},

			/**
			 * Revoke a token on tyk api
			 * @param {string} token hash
			 * @param {object} MouseEvent
			 */
			revokeToken: function(hash, e) {
				e.preventDefault();
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