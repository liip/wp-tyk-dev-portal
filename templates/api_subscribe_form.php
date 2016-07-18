<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" class="form-horizontal" method="post">

	<h2><?php _e('Dashboard', Tyk_Dev_Portal::TEXT_DOMAIN)?></h2>

	<h3><?php _e('My tokens', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>
	<div class="panel-body">
		<ul id="tyk-token-list" class="list-group">
			<li class="list-group-item" v-for="token in tokens">
				{{ token.token_name }}
				<div class="pull-right">
					<a href="#" class="btn text-danger" title="<?php _e('Revoke this token', Tyk_Dev_Portal::TEXT_DOMAIN)?>"><span class="glyphicon glyphicon-trash"></span></a>
				</div>
			</li>
		</ul>
	</div>

	<h3><?php _e('Request a token', Tyk_Dev_Portal::TEXT_DOMAIN)?></h3>

	<div>
		<div id="tyk-subscribe-success" class="hidden alert alert-info"></div>
		<div id="tyk-subscribe-error" class="hidden alert alert-danger"></div>
	</div>

	<div class="form-group">
		<label for="tyk-token-name" class="col-xs-2"><?php _e('Name', Tyk_Dev_Portal::TEXT_DOMAIN)?></label>
		<div class="col-xs-10">
			<input type="text" name="token_name" class="form-control" id="tyk-token-name" placeholder="<?php _e('Give this token a name', Tyk_Dev_Portal::TEXT_DOMAIN)?>" />
		</div>
	</div>	

	<div class="form-group">
		<label for="tyk-api-select" class="col-xs-2"><?php _e('API', Tyk_Dev_Portal::TEXT_DOMAIN)?></label>
		<div class="col-xs-10">
			<select name="api" id="tyk-api-select" class="form-control">
			<?php foreach (Tyk_API_Manager::available_apis() as $policy): ?>
				<option value="<?php print $policy['id']?>"><?php print $policy['name']?></option>
			<?php endforeach; ?>
			</select>
		</div>
	</div>


	<div class="form-group">
		<div class="col-xs-10 col-xs-offset-2">
			<input type="submit" id="btn-tyk-api-subscribe" class="btn btn-primary" value="<?php _e('Request an access token', Tyk_Dev_Portal::TEXT_DOMAIN)?>">						
		</div>
	</div>

</form>