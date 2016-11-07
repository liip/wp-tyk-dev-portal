<!-- quota tab -->
<div class="tab-pane" id="quota-tab">

	<quota-tab inline-template>
		<div class="row">
			<div class="col-md-8">		
				<h3><?php _e('Quota', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>
				<div class="input-group">
					<input type="text" class="form-control" v-model="key" placeholder="<?php _e('Please enter your token here', Tyk_Dev_Portal::TEXT_DOMAIN)?>" @keyup.enter="getQuotas()">
					<span class="input-group-btn">
						<button class="btn btn-default" :disabled="busy" @click="getQuotas()">
							<template v-if="busy">
								<?php _e('loading', Tyk_Dev_Portal::TEXT_DOMAIN)?>
							</template>
							<template v-else>
								<?php _e('Go', Tyk_Dev_Portal::TEXT_DOMAIN)?>
							</template>
						</button>
					</span>
				</div>

				<div style="margin-top: 1em;">
					<canvas v-el:chart></canvas>
				</div>
			</div>
		</div>

	</quota-tab>

</div><!-- /#quota-tab -->