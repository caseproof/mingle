<h3><?php _e('Request a Password Reset', 'mingle'); ?></h3>
<form name="mngl_forgot_password_form" id="mngl_forgot_password_form" action="" method="post">
	<p>
		<label><?php _e('Enter Your Username or Email Address', 'mingle'); ?><br/>
		<input type="text" name="mngl_user_or_email" id="mngl_user_or_email" class="input" value="<?php echo $mngl_user_or_email; ?>" tabindex="600" style="width: auto; min-width: 250px; font-size: 12px; padding: 4px;" /></label>
	</p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary mngl-share-button" value="<?php _e('Request Password Reset', 'mingle'); ?>" tabindex="610" />
		<input type="hidden" name="mngl_process_forgot_password_form" value="true" />
	</p>
</form>