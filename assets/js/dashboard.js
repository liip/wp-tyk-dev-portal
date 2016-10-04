/**
 * Tyk Dev Portal Dashbaord Components
 *
 * To keep things simple, we're keeping everything in one file for now.
 * When our js codebase grows, we should consider spilitting it up into modules
 * and adding a build step.
 */
;(function($, Vue, Chart, _) {
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
				busy: false
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
				this.busy = true;
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
						self.busy = false;
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
	 * Usage quota tab component
	 */
	var UsageTab = Vue.extend({
		props: ['tokens'],

		data: function() {
			return {
				key: null,
				busy: false,
				token: null
			}
		},

		watch: {
			token: function(token) {
				this.fetchUsage(token);
			}
		},

		events: {
			showUsage: function(token) {
				this.fetchUsage(token.hash);
			}
		},

		methods: {
			/**
			 * Get token usage data from server
			 */
			getQuotas: function() {
				var self = this;
				this.busy = true;

				var data = {
					action: 'get_token_quota',
					token: this.key
				};

				$.post(scriptParams.actionUrl, data)
					.done(function(response) {
						if (response.data) {
							self.showQuotas(response.data);
						}
					});
			},

			/**
			 * Show usage quota
			 * @param {object} data
			 */
			showQuotas: function(data) {
				var self = this;

				this.$nextTick(function() {
					new Chart(this.$els.chart, {
						type: 'doughnut',
						data: {
							labels: ['Used', 'Remaining'],
							datasets: [{
								data: [(data.quota_max-data.quota_remaining), data.quota_remaining],
								backgroundColor: ['#ffc115', '#05348B']
							}]
						}
					});
					this.busy = false;
				});
			},

			/**
			 * Fetch usage data from server and display it
			 */
			fetchUsage: function(hash) {
				var self = this;
				var data = {
					action: 'get_token_usage',
					token: hash
				};
				$.get(scriptParams.actionUrl, data)
					.done(function(response) {
						if (response.data) {
							self.showUsage(response.data);
						}
					});
			},

			/**
			 * Show usage data
			 * @return {[type]}
			 */
			showUsage: function(data) {
				var success = [],
					errors  = [],
					labels  = [];

				// get each "success" and "error" stat from the stack
				_.each(data, function(stat) {
					success.push(stat.success);
					errors.push(stat.error);
					if (stat.id) {
						// @todo localize this?
						labels.push(stat.id.day + '.' + stat.id.month + '.');
					}
				});

				// build a line chart on next view render with this data
				this.$nextTick(function() {
					new Chart(this.$els.usage, {
						type: 'line',
						data: {
							labels: labels,
							datasets: [
								{
									data: success,
									backgroundColor: 'rgba(5,52,139,.6)',
									label: 'Success',
								},
								{
									data: errors,
									backgroundColor: 'rgba(255,193,21,.6)',
									label: 'Errors',
								},
							]
						},
						options: {
							spanGaps: true
						}
					});
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
			'request-token-form': RequestTokenForm,
			'usage-tab': UsageTab
		},
		
		data: {
			tokens: [],
			message: '',
			hasError: false,
			loading: false,
			availableApis: []
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
			},

			/**
			 * Activate usage tab
			 */
			showUsageTab: function(token) {
				this.$broadcast('showUsage', token);
				$(this.$els.usageTab).tab('show');
			},	
		}
	});
})(jQuery, Vue, Chart, _);