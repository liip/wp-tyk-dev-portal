<!-- usage tab -->
<div class="tab-pane" id="usage-tab">

	<usage-tab inline-template :tokens="tokens">
		<div class="row">
			<div class="col-md-8">
				<h3><?php _e('Token', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>
				<select class="form-control" v-model="form.token">
					<option value=""><?php _e('-- please choose', Tyk_Dev_Portal::TEXT_DOMAIN)?></option>
					<option v-for="token in tokens" value="{{ token.hash }}">{{ token.token_name }}</option>
				</select>
				
				<div class="row">
					<div class="col-xs-6">
						<label for="tyk-usage-from"><?php _e('From', Tyk_Dev_Portal::TEXT_DOMAIN)?></label>
						<input id="tyk-usage-from" type="date" class="form-control" v-model="form.fromDate" @blur="fetchUsage">
					</div>

					<div class="col-xs-6">
						<label for="tyk-usage-to"><?php _e('To', Tyk_Dev_Portal::TEXT_DOMAIN)?></label>
						<input id="tyk-usage-to" type="date" class="form-control" v-model="form.toDate" @blur="fetchUsage">
					</div>
				</div>

				<canvas v-el:usage></canvas>
			</div>
		</div>

	</usage-tab>

</div><!-- /#usage-tab -->