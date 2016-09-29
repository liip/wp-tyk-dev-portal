/**
 * Tyk Dev Portal Dashbaord Components
 *
 * As this is currently all we have in terms of js, we're keeping everything in one
 * file. When our js codebase grows, we should consider spilitting it up into modules
 * and adding a build step.
 */
;(function($, Vue) {
	/**
	 * Request token form component
	 */
	var RequestTokenForm = Vue.extend({
		props: ['apis', 'subscribedApis'],

		data: function() {
			return {
				token_name: '',
				api: '',
				message: '',
				hasError: false,
				inProgress: false
			};
		},

		events: {
			/**
			 * Reset form fields after token was created
			 */
			'new-token': function() {
				this.token_name = '';
				this.api = '';
			}
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
			 * Check if user is already subscribed to an api
			 * @return {boolean}
			 */
			hasTokenForAPI: function(apiId) {
				return ($.inArray(apiId, this.subscribedApis) >= 0);
			},

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
			tokens: [],
			message: '',
			hasError: false,
			loading: false,
			availableApis: [],
		},

		computed: {
			/**
			 * Get a list of subscribed APIs
			 * @return {array}
			 */
			subscribedApis: function() {
				return _.pluck(this.tokens, 'api_id');
			}
		},
		
		events: {
			/**
			 * Request token form got a new token: refresh token list
			 */
			'new-token': function() {
				this.updateFromServer();
			},

			/**
			 * A token was deleted: refresh token list
			 */
			'deleted-token': function() {
				this.updateFromServer();
			}
		},
		
		beforeCompile: function() {
			this.updateFromServer();
		},

		methods: {
			/**
			 * Update all data from server
			 * @return {void}
			 */
			updateFromServer: function() {
				var self = this;
				this.loading = true;
				$.when( this.fetchTokens(), this.fetchApis() ).then(function() {
					self.loading = false;
				});
			},

			/**
			 * Get api name from id
			 * @return {string}
			 */
			getApiName: function(apiId) {
				var api = _.findWhere(this.availableApis, { id: apiId });
				return _.isObject(api)
					? api.name
					: '';
			},

			/**
			 * Fetch tokens from server
			 * @return {object} jQuery Promise
			 */
			fetchTokens: function() {
				var self = this;
				return $.getJSON(scriptParams.actionUrl, {action: 'get_tokens'}).done(function(result) {
					if (typeof(result) == 'object' && result.success) {
						self.tokens = result.data;
					}
					else {
						self.hasError = true;
						if (console && console.error) {
							console.error(result);
						}
					}
				});
			},

			/**
			 * Fetch available apis from server
			 * @return {object} jQuery Promise
			 */
			fetchApis: function() {
				var self = this;
				return $.getJSON(scriptParams.actionUrl, {action: 'get_available_apis'}).done(function(result) {
					if (typeof(result) == 'object' && result.success) {
						self.availableApis = result.data;
					}
					else {
						self.hasError = true;
						if (console && console.error) {
							console.error(result);
						}
					}
				});
			},

			/**
			 * Revoke a token on tyk api
			 * @param {object} token
			 */
			revokeToken: function(token) {
				var data = {
					action: 'revoke_token',
					token: token.hash
				};
				var self = this;
				$.post(scriptParams.actionUrl, data)
					.done(function(result) {
						if (result && result.success) {
							self.message = result.data.message;
							self.$emit('deleted-token');
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