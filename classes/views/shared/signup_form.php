<form name="registerform" id="registerform" action="" method="post">
<input type="hidden" id="mngl-process-form" name="mngl-process-form" value="Y" />
<p>
	<label><?php _e('Username', 'mingle'); ?>*:<br />
	<input type="text" name="user_login" id="user_login" class="input mngl_signup_input" value="<?php echo $user_login; ?>" size="20" tabindex="200" /></label>
</p>
<p>
	<label><?php _e('E-mail', 'mingle'); ?>*:<br />
	<input type="text" name="user_email" id="user_email" class="input mngl_signup_input" value="<?php echo $user_email; ?>" size="25" tabindex="300" /></label>
</p>
<?php if(isset($mngl_options->field_visibilities['signup_page']['name'])) { ?>
  <p>
	  <label><?php _e('First Name', 'mingle'); ?>:<br />
	  <input type="text" name="user_first_name" id="user_first_name" class="input mngl_signup_input" value="<?php echo $user_first_name; ?>" size="20" tabindex="400" /></label>
  </p>  
  <p>
    <label><?php _e('Last Name', 'mingle'); ?>:<br />
    <input type="text" name="user_last_name" id="user_last_name" class="input mngl_signup_input" value="<?php echo $user_last_name; ?>" size="20" tabindex="500" /></label>
  </p>
<?php } ?>
<?php if(isset($mngl_options->field_visibilities['signup_page']['url'])) { ?>
  <p>
    <label><?php _e('Website', 'mingle'); ?>:<br />
    <input type="text" name="mngl_user_url" id="mngl_user_url" value="<?php echo $mngl_user_url; ?>" class="input mngl_signup_input" size="20" tabindex="600"/></label>
  </p>
<?php } ?>
<?php if(isset($mngl_options->field_visibilities['signup_page']['location'])) { ?>
  <p>
    <label><?php _e('Location', 'mingle'); ?>:<br />
    <input type="text" name="mngl_user_location" id="mngl_user_location" value="<?php echo $mngl_user_location; ?>" class="input mngl_signup_input" size="20" tabindex="700" /></label>
  </p>
<?php } ?>
<?php if(isset($mngl_options->field_visibilities['signup_page']['bio'])) { ?>
  <p>
    <label><?php _e('Bio', 'mingle'); ?>:<br />
    <textarea name="mngl_user_bio" id="mngl_user_bio" class="input mngl-growable mngl_signup_input" tabindex="800"><?php echo wptexturize($mngl_user_bio); ?></textarea></label>
  </p>
<?php } ?>  
<?php if(isset($mngl_options->field_visibilities['signup_page']['sex'])) { ?>
  <p>
    <label><?php _e('Gender', 'mingle'); ?>*:&nbsp;<?php echo MnglProfileHelper::sex_dropdown('mngl_user_sex', $mngl_user_sex, '', 900); ?></label>
  </p>
<?php } ?>

<?php if(isset($mngl_options->field_visibilities['signup_page']['password'])) { ?>
  <p>
    <label><?php _e('Password', 'mingle'); ?>:<br/>
    <input type="password" name="mngl_user_password" id="mngl_user_password" class="input mngl_signup_input" tabindex="1000"/></label>
  </p>
  <p>
    <label><?php _e('Password Confirmation', 'mingle'); ?>:<br />
    <input type="password" name="mngl_user_password_confirm" id="mngl_user_password_confirm" class="input mngl_signup_input" tabindex="1100"/></label>
  </p>
<?php } else { ?>
	<p id="reg_passmail"><?php _e('A password will be e-mailed to you.', 'mingle'); ?></p>
<?php } ?>
<?php if($mngl_options->signup_captcha) { ?>
<?php
   $captcha_code = MnglUtils::str_encrypt(MnglUtils::generate_random_code(6));
?>
<p>
<label><?php _e('Enter Captcha Text', 'mingle'); ?>*:<br />
<img src="<?php echo MNGL_SCRIPT_URL; ?>&controller=captcha&action=display&width=120&height=40&code=<?php echo $captcha_code; ?>" /><br/>
<input id="security_code" name="security_code" style="width:120px" type="text" tabindex="1200" />
<input type="hidden" name="security_check" value="<?php echo $captcha_code; ?>">
</p>
<?php } ?>
  <?php do_action('mngl-user-signup-fields'); ?>

	<br class="clear" />
	<p class="submit"><input type="submit" name="wp-submit" id="wp-submit" class="mngl-share-button" value="<?php _e('Sign Up', 'mingle'); ?>" tabindex="60" /></p>
</form>
