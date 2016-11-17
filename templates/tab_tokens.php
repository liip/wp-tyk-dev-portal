<div class="tab-pane active" id="tokens-tab">

	<!-- list of user tokens -->
	<h3><?php _e('My tokens', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>

	<!-- area for messages -->
	<div v-cloak>
		<div id="tyk-subscribe-success" class="alert alert-info" v-if="message" role="alert">
			{{ message }}
		</div>
		<div id="tyk-subscribe-error" class="alert alert-danger" v-if="hasError" role="alert">
			<?php _e('An error occurred. Please try again.', Tyk_Dev_Portal::TEXT_DOMAIN)?>
		</div>
	</div>

	<table class="table">
		<template v-if="tokens.length">
			<thead>
				<tr>
					<th><?php _e('Name', Tyk_Dev_Portal::TEXT_DOMAIN)?></th>
					<th><?php _e('API', Tyk_Dev_Portal::TEXT_DOMAIN)?></th>
					<th class="icon"></th>
				</tr>
			</thead>
			<tbody>
				<tr v-for="token in tokens">
					<td>{{ token.token_name }}</td>
					<td>{{ getApiName(token.api_id) }}</td>
					<td>
						<a href="#" @click.prevent="showUsageTab(token)" class="btn text-info" title="<?php _e('Show usage', Tyk_Dev_Portal::TEXT_DOMAIN)?>">
							<span class="glyphicon glyphicon-stats"></span>
						</a>
						<a href="#revoke" @click.prevent="revokeToken(token)" class="btn text-danger" title="<?php _e('Revoke this token', Tyk_Dev_Portal::TEXT_DOMAIN)?>">
							<span class="glyphicon glyphicon-trash"></span>
						</a>
					</td>
				</tr>
			</tbody>
		</template>
		<tbody v-else>
			<tr v-if="loading">
				<td colspan="3"><?php _e("loading", Tyk_Dev_Portal::TEXT_DOMAIN)?></td>
			</tr>
			<tr v-else>
				<td colspan="3"><?php _e("You don't have any tokens yet", Tyk_Dev_Portal::TEXT_DOMAIN)?></td>
			</tr>
		</tbody>
	</table>

	<div class="row">
		<div class="col-md-8">

			<!-- request an access token for an api -->
			<request-token-form inline-template :apis="availableApis" :subscribed-apis.sync="subscribedApis">
				<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" class="form-horizontal" method="post">
					<h3><?php _e('Request a token', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>

					<!-- area for messages -->
					<div v-cloak>
						<div id="tyk-subscribe-success" class="alert alert-info" v-if="message" role="alert">
							<button type="button" class="close" data-dismiss="alert" aria-label="Close" @click="closeMessage"><span aria-hidden="true">&times;</span></button>
							{{message}}
						</div>
						<div id="tyk-subscribe-error" class="alert alert-danger" v-if="hasError" role="alert">
							<?php _e('An error occurred. Please try again.', Tyk_Dev_Portal::TEXT_DOMAIN)?>
						</div>
					</div>

					<div class="form-group">
						<label for="tyk-token-name" class="col-xs-2"><?php _e('Name', Tyk_Dev_Portal::TEXT_DOMAIN)?></label>
						<div class="col-xs-10">
							<input type="text" v-model="token_name" name="token_name" class="form-control" id="tyk-token-name" placeholder="<?php _e('Give this token a name', Tyk_Dev_Portal::TEXT_DOMAIN)?>" />
						</div>
					</div>	

					<div class="form-group">
						<label for="tyk-api-select" class="col-xs-2"><?php _e('API', Tyk_Dev_Portal::TEXT_DOMAIN)?></label>
						<div class="col-xs-10">
							<select name="api" id="tyk-api-select" class="form-control" v-model="api">
								<option value=""><?php _e('-- please choose', Tyk_Dev_Portal::TEXT_DOMAIN)?></option>
								<option v-for="api in apis" value="{{ api.id }}" :disabled="hasTokenForAPI(api.id)">{{ api.name }}</option>
							</select>
						</div>
					</div>

					<div class="form-group">
						<div class="col-xs-10 col-xs-offset-2">
							<button @click.prevent="register" :disabled="busy || !formValid" id="btn-tyk-api-subscribe" class="btn btn-primary">
								<template v-if="busy">
									<?php _e('loading', Tyk_Dev_Portal::TEXT_DOMAIN)?>
								</template>
								<template v-else>
									<?php _e('Request a token', Tyk_Dev_Portal::TEXT_DOMAIN)?>
								</template>
							</button>
						</div>
					</div>
				</form>
			</request-token-form>
		</div>
	</div>
</div> <!-- /#tokens -->