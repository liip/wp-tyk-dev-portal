<style>
	[v-cloak] { display: none; }
	th.icon { width: 110px; }
</style>

<div id="tyk-dashboard">

	<ul class="nav nav-tabs" role="tablist">
		<li role="presentation" class="active">
			<a href="#tokens-tab" aria-controls="tokens-tab" role="tab" data-toggle="tab"><?php _e('Tokens', Tyk_Dev_Portal::TEXT_DOMAIN)?></a>
		</li>
		<li role="presentation">
			<a href="#usage-tab" v-el:usage-tab aria-controls="usage-tab" role="tab" data-toggle="tab"><?php _e('Usage', Tyk_Dev_Portal::TEXT_DOMAIN)?></a>
		</li>
		<li role="presentation">
			<a href="#quota-tab" v-el:quoata-tab aria-controls="quoata-tab" role="tab" data-toggle="tab"><?php _e('Quota', Tyk_Dev_Portal::TEXT_DOMAIN)?></a>
		</li>
	</ul>

	<div class="tab-content">

		<?php include_once TYK_DEV_PORTAL_TPL_PATH . '/tab_tokens.php'; ?>		

		<?php include_once TYK_DEV_PORTAL_TPL_PATH . '/tab_usage.php'; ?>

		<?php include_once TYK_DEV_PORTAL_TPL_PATH . '/tab_quota.php'; ?>

	</div>
</div>