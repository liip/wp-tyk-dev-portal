/**
 * Tyk Dev Portal Dashbaord Components
 *
 * To keep things simple, we're keeping everything in one file for now.
 * When our js codebase grows, we should consider spilitting it up into modules
 * and adding a build step.
 */
;(function($, Vue, Chart, _) {
	/**
	 * Add a leading zero to a number if it's not there yet
	 * @param {int} nr
	 * @return {string}
	 */
	function leadingZero(nr) {
		return ('0' + nr).slice(-2);
	}

	/**
	 * Add two numbers
	 * @param {number} a
	 * @param {number} b
	 * @return {number} Sum of a and b
	 */
	function add(a, b) { 
		return a+b 
	}

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
	 * Usage tab component
	 */
	var UsageTab = Vue.extend({
		props: ['tokens'],

		data: function() {
			// look at all this code! all just to format two dates to set initial values for the date fields :0
			var now = new Date(),
				nextWeek = new Date();
			nextWeek.setDate(now.getDate() - 7);
			var fromDate = [nextWeek.getFullYear(), leadingZero(nextWeek.getMonth()+1), leadingZero(nextWeek.getDate())].join('-'),
				toDate = [now.getFullYear(), leadingZero(now.getMonth()+1), leadingZero(now.getDate())].join('-');

			return {
				// instance of chartist line chart
				lineChart: null,
				error: null,
				form: {
					token: null,
					fromDate: fromDate,
					toDate: toDate,
					// is the form busy?
					busy: false,
				}
			}
		},

		watch: {
			'form.token': function() {
				this.fetchUsage();
			}
		},

		events: {
			// list for an event from parent to show data
			showUsage: function(token) {
				// this will trigger a reload of the data
				this.form.token = token.hash;
			}
		},

		methods: {
			/**
			 * Fetch usage data from server and display it
			 * Uses this.form params to filter data
			 */
			fetchUsage: function() {
				var self = this;
				var data = {
					action: 'get_token_usage',
					token: this.form.token
				};

				// add from date
				if (this.form.fromDate) {
					data.from = this.form.fromDate;
				}
				// add to date
				if (this.form.toDate) {
					data.to = this.form.toDate;
				}

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
				var chartData = this.crunchUsageNumbers(data);

				// setup the chart data
				var chartConfig = {
					labels: chartData.labels,
					datasets: [
						{
							data: chartData.success,	
							backgroundColor: 'rgba(5,52,139,.6)',
							label: scriptParams.label_success,
						},
						{
							data: chartData.errors,
							backgroundColor: 'rgba(255,193,21,.6)',
							label: scriptParams.label_errors,
						},
					]
				};

				// build a line chart on next view render with this data
				this.$nextTick(function() {
					// init chart from scratch
					if (!this.lineChart) {
						this.lineChart = new Chart(this.$els.usage, {
							type: 'line',
							data: chartConfig,
							options: {
								spanGaps: true,
								scales: {
									yAxes: [{ ticks: { min: 0 }}]
								}
							}
						});	
					}
					// just update the data
					else {
						this.lineChart.config.data = chartConfig;
					}

					// start y scale at 0 and increment in steps of 1
					if (chartData.sum.total < 1) {
						this.lineChart.config.options.scales.yAxes[0].ticks.stepSize = 1;
					}
					this.lineChart.update();
				});
			},

			/**
			 * Crunch the numbers from usage data and gain insightful information :)
			 * @param {object} data
			 * @return {object}
			 */
			crunchUsageNumbers: function(data) {
				var chartData = {
					success: [],
					errors: [],
					labels: [],
					sum: {}
				};

				// get each "success" and "error" stat from the stack
				_.chain(data)
					// sort by timestamp first
					.sortBy(function(item) {
						return (new Date([item.id.year, item.id.month, item.id.day].join('-'))).getTime();
					})
					// get number for each entry
					.each(function(stat) {
						chartData.success.push(stat.success);
						chartData.errors.push(stat.error);
						if (stat.id) {
							// @todo localize this?
							chartData.labels.push(stat.id.day + '.' + stat.id.month + '.');
						}
					});

 				chartData.sum.success = _.reduce(chartData.success, add),
 				chartData.sum.errors  = _.reduce(chartData.errors, add);
 				chartData.sum.total   = add(chartData.sum.success, chartData.sum.errors);

 				return chartData;
			}
		}
	});


	/**
	 * Quota tab component
	 */
	var QuotaTab = Vue.extend({
		data: function() {
			return {
				// the selected token key
				key: null,
				busy: false,
				error: null
			}
		},

		watch: {
			'key': function() {
				if (this.key.length > 0) {
					this.getQuotas();
				}
			}
		},

		methods: {
			/**
			 * Get token usage data from server
			 */
			getQuotas: function() {
				var self = this;
				this.busy = true;
				this.error = null;

				var data = {
					action: 'get_token_quota',
					token: this.key
				};

				// use a post request so the key doesn't show up in server logs
				$.post(scriptParams.actionUrl, data)
					.done(function(response) {
						if (response.data && response.success) {
							self.showQuotas(response.data);
						}
						else if (response.success === false) {
							self.setError(response.data)
						}
					});
			},

			/**
			 * Set an error in gui
			 * @param {string} error
			 */
			setError: function(error) {
				this.error = error;
				this.busy = false;
			},

			/**
			 * Show usage quota
			 * @param {object} data
			 */
			showQuotas: function(data) {
				// catch invalid data
				if (!data.quota_max || data.quota_max < data.quota_remaining) {
					this.setError(scriptParams.error_invalid_data)
					return;
				}

				this.$nextTick(function() {
					new Chart(this.$els.chart, {
						type: 'doughnut',
						data: {
							labels: [scriptParams.label_used, scriptParams.label_remaining],
							datasets: [{
								data: [(data.quota_max - data.quota_remaining), data.quota_remaining],
								backgroundColor: ['#ffc115', '#05348B']
							}]
						}
					});
					this.busy = false;
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
			'usage-tab': UsageTab,
			'quota-tab': QuotaTab
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