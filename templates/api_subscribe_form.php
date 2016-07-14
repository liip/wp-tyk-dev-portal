<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" class="form-horizontal" method="post">

	<h2><?php _e('Dashboard')?></h2>

	<div>
		<div id="tyk-subscribe-success"></div>
		<div id="tyk-subscribe-error"></div>
	</div>
	
	<div class="form-group">
		<label class="col-xs-2" for="tyk-api-select"><?php _e('API')?></label>
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
			<input type="submit" id="btn-tyk-api-subscribe" class="btn btn-primary" value="<?php _e('Request an access token')?>">						
		</div>
	</div>

</form>