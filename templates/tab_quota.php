<!-- quota tab -->
<div class="tab-pane" id="quota-tab">

	<quota-tab inline-template>
		<div class="row">
			<div class="col-md-8">		
				<h3><?php _e('Remaining quota', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>
					<input 
						type="text" 
						class="form-control" 
						v-model="key" 
						placeholder="<?php _e('Please enter your token here', Tyk_Dev_Portal::TEXT_DOMAIN)?>" @keyup.enter="getQuotas()">
					<span class="help-block" v-show="busy"><?php _e('loading', Tyk_Dev_Portal::TEXT_DOMAIN)?></span>

				<div style="margin-top: 1em;">
					<div class="alert alert-danger" v-show="error">{{error}}</div>
					<canvas v-el:chart></canvas>
				</div>
			</div>
		</div>

	</quota-tab>

</div><!-- /#quota-tab -->