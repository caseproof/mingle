<h3><?php _e('Enter your new password', 'mingle'); ?></h3>
<form name="mngl_reset_password_form" id="mngl_reset_password_form" action="" method="post">
  <p>
    <label><?php _e('Password', 'mingle'); ?>:<br/>
    <input type="password" name="mngl_user_password" id="mngl_user_password" class="input mngl_signup_input" tabindex="700"/></label>
  </p>
  <p>
    <label><?php _e('Password Confirmation', 'mingle'); ?>:<br />
    <input type="password" name="mngl_user_password_confirm" id="mngl_user_password_confirm" class="input mngl_signup_input" tabindex="710"/></label>
  </p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary mngl-share-button" value="<?php _e('Reset Password', 'mingle'); ?>" tabindex="720" />
		<input type="hidden" name="action" value="mngl_process_reset_password_form" />
		<input type="hidden" name="mngl_screenname" value="<?php echo $mngl_screenname; ?>" />
  	<input type="hidden" name="mngl_key" value="<?php echo $mngl_key; ?>" />
	</p>
</form>