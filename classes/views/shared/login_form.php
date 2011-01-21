<form name="loginform" id="loginform" action="<?php echo $login_page; ?>" method="post">
	<p>
		<label><strong><?php _e('Username', 'mingle'); ?></strong><br/>
		<input type="text" name="log" id="user_login" class="input" value="<?php echo (isset($_POST['log'])?$_POST['log']:''); ?>" tabindex="500" style="width: auto; min-width: 250px; font-size: 12px; padding: 4px;" /></label><br/>
		<label><strong><?php _e('Password', 'mingle'); ?></strong><br/>
		<input type="password" name="pwd" id="user_pass" class="input" value="<?php echo (isset($_POST['pwd'])?$_POST['pwd']:''); ?>" tabindex="510" style="width: auto; min-width: 250px; line-height: 12px; padding: 4px;" /></label><br/>
	  <label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="520" style="width: 15px;"<?php echo (isset($_POST['rememberme'])?' checked="checked"':''); ?> /> <?php _e('Remember Me', 'mingle'); ?></label>
	</p>
	<p class="submit">
		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary mngl-share-button" value="<?php _e('Log In', 'mingle'); ?>" tabindex="530" />
		<input type="hidden" name="redirect_to" value="<?php echo $redirect_to; ?>" />
		<input type="hidden" name="testcookie" value="1" />
		<input type="hidden" name="mngl_process_login_form" value="true" />
	</p>
</form>
<p style="font-size: 10px" class="mngl-login-actions">
<?php if(get_option('users_can_register')) {
?>
    <a href="<?php echo $signup_url; ?>"><?php _e('Register', 'mingle'); ?></a>&nbsp;|
<?php
      }
?>
<a href="<?php echo $forgot_password_url; ?>"><?php _e('Lost Password?', 'mingle'); ?></a>
</p>