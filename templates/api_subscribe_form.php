<style>
	[v-cloak] { display: none; }
</style>

<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" class="form-horizontal" method="post">


	<h2><?php _e('Dashboard', Tyk_Dev_Portal::TEXT_DOMAIN)?></h2>

	<!-- list of user tokens -->
	<div id="tyk-token-list">
		<h3><?php _e('My tokens', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>

		<!-- area for messages -->
		<div v-cloak>
			<div id="tyk-subscribe-success" class="alert alert-info" v-if="message">
				{{message}}
			</div>
			<div id="tyk-subscribe-error" class="alert alert-danger" v-if="hasError" >
				<?php _e('An error occurred. Please try again.', Tyk_Dev_Portal::TEXT_DOMAIN)?>
			</div>
		</div>

		<div class="panel-body">
			<ul class="list-group">
				<li class="list-group-item" v-if="loading"><?php _e("Loading", Tyk_Dev_Portal::TEXT_DOMAIN)?>...</li>
				<template v-else>
					<template v-if="tokens">
						<li class="list-group-item" v-for="token in tokens">
							{{ token.token_name }}
							<div class="pull-right">
								<a href="#revoke" v-on:click.prevent="revokeToken(token.hash)" class="btn text-danger" title="<?php _e('Revoke this token', Tyk_Dev_Portal::TEXT_DOMAIN)?>"><span class="glyphicon glyphicon-trash"></span></a>
							</div>
						</li>
					</template>
					<li class="list-group-item" v-else><?php _e("You don't have any tokens yet", Tyk_Dev_Portal::TEXT_DOMAIN)?></li>
				</template>
			</ul>
		</div>
	</div>


	<!-- request an access token for an api -->
	<div id="tyk-request-token">
		<h3><?php _e('Request a token', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>

		<!-- area for messages -->
		<div v-cloak>
			<div id="tyk-subscribe-success" class="alert alert-info" v-if="message">
				{{message}}
			</div>
			<div id="tyk-subscribe-error" class="alert alert-danger" v-if="hasError" >
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
				<?php foreach (Tyk_API_Manager::available_apis() as $policy): ?>
					<option value="<?php print $policy['id']?>"><?php print $policy['name']?></option>
				<?php endforeach; ?>
				</select>
			</div>
		</div>

		<div class="form-group">
			<div class="col-xs-10 col-xs-offset-2">
				<button v-on:click.prevent="register" :disabled="inProgress || !formValid" id="btn-tyk-api-subscribe" class="btn btn-primary">
					<template v-if="inProgress">
						<?php _e('loading...', Tyk_Dev_Portal::TEXT_DOMAIN)?>
					</template>
					<template v-else>
						<?php _e('Request an access token', Tyk_Dev_Portal::TEXT_DOMAIN)?>
					</template>
				</button>
			</div>
		</div>
	</div>

</form>