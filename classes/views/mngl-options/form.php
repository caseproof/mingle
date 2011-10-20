<div class="wrap">
<h2 id="mngl_title" style="margin: 10px 0px 0px 0px; padding: 0px 0px 0px 56px; height: 48px; background: url(<?php echo MNGL_URL . "/images/mingle_48.png"; ?>) no-repeat"><?php _e('Mingle: Options', 'mingle'); ?></h2>
<br/>

<form name="mngl_options_form" method="post" action="">
<input type="hidden" name="action" value="process-form">
<?php wp_nonce_field('update-options'); ?>

<h3><?php _e('Mingle Pages', 'mingle'); ?>:</h3>
<span class="description"><?php printf(__('Before you can get going with Mingle, you must configure where Mingle pages on your website will appear. You\'ll want to %1$screate a new page%2$s for each of these pages that mingle needs to work. You should give your page a title and optionally put some content into the page ... just know that once you set the page up here, the page\'s content will not display.', 'mingle'), '<a href="page-new.php">', '</a>'); ?></span>
<table class="form-table">
  <tr class="form-field">
    <td valign="top" style="text-align: right; width: 150px;"><?php _e('Profile Page', 'mingle'); ?>*: </td>
    <td style="width: 150px;">
      <?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->profile_page_id_str, $mngl_options->profile_page_id, __("Profile", 'mingle') )?>
    </td>
    <td valign="top" style="text-align: right; width: 150px;"><?php _e('Activity Page', 'mingle'); ?>*: </td>
    <td style="width: 150px;">
      <?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->activity_page_id_str, $mngl_options->activity_page_id, __("Activity", 'mingle') )?>
    </td>
    <td>&nbsp;</td>
  </tr>
  <tr class="form-field">
    <td valign="top" style="text-align: right;"><?php _e('Profile Edit Page', 'mingle'); ?>*: </td>
    <td>
      <?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->profile_edit_page_id_str, $mngl_options->profile_edit_page_id, __("Account", 'mingle') )?>
    </td>
    <td valign="top" style="text-align: right;"><?php _e('Directory Page', 'mingle'); ?>: </td>
    <td>
      <?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->directory_page_id_str, $mngl_options->directory_page_id, __("Directory", 'mingle'), true )?>
    </td>
    <td>&nbsp;</td>
  </tr>
  <tr class="form-field">
    <td valign="top" style="text-align: right;"><?php _e('Friends Page', 'mingle'); ?>*: </td>
    <td>
      <?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->friends_page_id_str, $mngl_options->friends_page_id, __("Directory", 'mingle') )?>
    </td>
    <td valign="top" style="text-align: right;"><?php _e('Login Page', 'mingle'); ?>: </td>
    <td>
      <?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->login_page_id_str, $mngl_options->login_page_id, __("Login", 'mingle'), true )?>
    </td>
    <td>&nbsp;</td>
  </tr>
  <tr class="form-field">
    <td valign="top" style="text-align: right;"><?php _e('Friend Requests Page', 'mingle'); ?>*: </td>
    <td>
      <?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->friend_requests_page_id_str, $mngl_options->friend_requests_page_id, __("Friends", 'mingle') )?>
    </td>
    <td valign="top" style="text-align: right;"><?php _e('Signup Page', 'mingle'); ?>: </td>
    <td>
      <?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->signup_page_id_str, $mngl_options->signup_page_id, __("Signup", 'mingle'), true )?>
    </td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" style="text-align: right;"><?php _e('Inbox Page', 'mingle'); ?>*: </td>
    <td><?php MnglOptionsHelper::wp_pages_dropdown( $mngl_options->inbox_page_id_str, $mngl_options->inbox_page_id, __("Inbox", 'mingle') )?></td>
    <td valign="top" style="text-align: right;">&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
</table>

<?php do_action('mngl_option_pages'); ?>

<h4><?php _e('Profile Options', 'mingle'); ?>:</h4>
<div class="mngl-options-pane">
<p><label for="<?php echo $mngl_options->pretty_profile_urls_str; ?>"><input type="checkbox" name="<?php echo $mngl_options->pretty_profile_urls_str; ?>" id="<?php echo $mngl_options->pretty_profile_urls_str; ?>"<?php echo (($mngl_options->pretty_profile_urls)?' checked="checked"':''); ?>/>&nbsp;<?php _e('Pretty Profile Urls','mingle'); ?></label><br/>
<span class="description"><?php _e('When checked, Pretty Profile Urls will allow users to type their screenname following your site\'s domain name for their url. Note, if you do not have Apache rewrite functioning and have not selected something other than "Default" under your General Permalink settings, this will not work.', 'mingle'); ?></span></p>
<p><label for="<?php echo $mngl_options->signup_spam_email_protection_str; ?>"><input type="checkbox" name="<?php echo $mngl_options->signup_spam_email_protection_str; ?>" id="<?php echo $mngl_options->signup_spam_email_protection_str; ?>"<?php echo (($mngl_options->signup_spam_email_protection)?' checked="checked"':''); ?>/>&nbsp;<?php _e('User Registration Email Spam Protection','mingle'); ?></label><br/>
<span class="description"><?php _e('When checked, Mingle will use advanced email lookup techniques to validate the user signup to prevent against user registration spam.', 'mingle'); ?></span></p>
<p><label for="<?php echo $mngl_options->signup_robot_protection_str; ?>"><input type="checkbox" name="<?php echo $mngl_options->signup_robot_protection_str; ?>" id="<?php echo $mngl_options->signup_robot_protection_str; ?>"<?php echo (($mngl_options->signup_robot_protection)?' checked="checked"':''); ?>/>&nbsp;<?php _e('Block Robots from Registering as Users','mingle'); ?></label><br/>
<span class="description"><?php _e('When checked, Mingle will attempt to identify the user as legitimate or as a robot (computer program) if it\'s a robot then registration will be prevented.', 'mingle'); ?></span></p>
<p><label for="<?php echo $mngl_options->signup_captcha_str; ?>"><input type="checkbox" name="<?php echo $mngl_options->signup_captcha_str; ?>" id="<?php echo $mngl_options->signup_captcha_str; ?>"<?php echo (($mngl_options->signup_captcha)?' checked="checked"':''); ?>/>&nbsp;<?php _e('Require Captcha On User Signup Page','mingle'); ?></label><br/>
<span class="description"><?php _e('When checked, Mingle will display an image captcha that the user will be required to complete in order to successfully register.', 'mingle'); ?></span></p>
<p><label for="<?php echo $mngl_options->prevent_admin_access_str; ?>"><input type="checkbox" name="<?php echo $mngl_options->prevent_admin_access_str; ?>" id="<?php echo $mngl_options->prevent_admin_access_str; ?>"<?php echo (($mngl_options->prevent_admin_access)?' checked="checked"':''); ?>/>&nbsp;<?php _e('Prevent Subscribers Access to the WordPress Admin','mingle'); ?></label><br/>
<span class="description"><?php _e('When checked, Mingle will redirect non-admin users to their activity page if attempting to go anywhere within /wp-admin.', 'mingle'); ?></span></p>
<p><label for="<?php echo $mngl_options->show_powered_by_str; ?>"><input name="<?php echo $mngl_options->show_powered_by_str; ?>" type="checkbox" id="<?php echo $mngl_options->show_powered_by_str; ?>"<?php echo (($mngl_options->show_powered_by)?' checked="checked"':''); ?>/>&nbsp;<?php _e('Show Powered by Mingle link in sidebar','mingle'); ?></label><br/>
<span class="description"><?php _e('When unchecked, it will remove the Mingle Powered by in sidebar.', 'mingle'); ?></span></p>
</div>

<h4><?php _e('Default Friends', 'mingle'); ?>:</h4>
<div class="mngl-options-pane">
<span class="description"><?php _e('These Users will be added as a friends to all new signups.', 'mingle'); ?></span>
  <table class="form-table mngl-default-friends-table" style="width: auto;">
<?php

  if(count($mngl_options->default_friends) > 0)
  {
    foreach($mngl_options->default_friends as $default_friend)
    {
      $default_friend = (int)$default_friend;
      if($default_friend and !empty($default_friend))
        $this->display_default_friend_drop_down($default_friend);
    }
  }
  
?>
  </table>
  <p><a href="javascript:mngl_add_default_user();" class="button">+ <?php _e('Add a Default Friend', 'mingle'); ?></a></p>
</div>

<h4><?php _e('Invisible Users', 'mingle'); ?>:</h4>
<div class="mngl-options-pane">
<span class="description"><?php _e('Any users checked below will not be visible to Mingle. They won\'t have a profile page, friends, be listed in the directory or show up anywhere in mingle.', 'mingle'); ?></span>
<p><?php MnglOptionsHelper::users_multiselect($mngl_options->invisible_users_str . "[]", $mngl_options->invisible_users); ?><br/><span class="description"><?php _e('Hold down Control Key (the Command Key if you\'re on a Mac) or the Shift Key to select multiple users.', 'mingle'); ?></span></p>
</div>

<h4><?php _e('Field Display Options', 'mingle'); ?>:</h4>
<div class="mngl-options-pane">
<span class="description"><?php _e("Configure the fields you'd like your users to see, and if they will be able to display them on their profiles. <code>Public</code> indicates that the field will be available to the users and that the value they enter into it will show up on their public profiles. <code>Private</code> indicates that the field will be available to the users but the value they enter into it will not show up on their public profiles. <code>Hidden</code> indicates that this field won't be visible to the users or on their public profiles.", 'mingle'); ?></span>
<table class="form-table">
  <tr class="form-field">
    <td valign="top"><?php _e('Show Name Fields', 'mingle'); ?>: </td>
    <td valign="top"><?php MnglOptionsHelper::display_field_visibility_buttons('name'); ?></td>
  </tr>
  <tr class="form-field">
    <td valign="top"><?php _e('Show Website Field', 'mingle'); ?>: </td>
    <td valign="top"><?php MnglOptionsHelper::display_field_visibility_buttons('url'); ?></td>
  </tr>
  <tr class="form-field">
    <td valign="top"><?php _e('Show Location Field', 'mingle'); ?>: </td>
    <td valign="top"><?php MnglOptionsHelper::display_field_visibility_buttons('location'); ?></td>
  </tr>
  <tr class="form-field">
    <td valign="top"><?php _e('Show Bio Field', 'mingle'); ?>: </td>
    <td valign="top"><?php MnglOptionsHelper::display_field_visibility_buttons('bio'); ?></td>
  </tr>
  <tr class="form-field">
    <td valign="top"><?php _e('Show Birthday Field', 'mingle'); ?>: </td>
    <td valign="top"><?php MnglOptionsHelper::display_field_visibility_buttons('birthday'); ?></td>
  </tr>
  <tr class="form-field">
    <td valign="top"><?php _e('Show Gender Field', 'mingle'); ?>: </td>
    <td valign="top"><?php MnglOptionsHelper::display_field_visibility_buttons('sex'); ?></td>
  </tr>
  <tr class="form-field">
    <td valign="top"><?php _e('Show Password Field', 'mingle'); ?>: </td>
    <td valign="top"><?php MnglOptionsHelper::display_field_visibility_buttons('password', false, true); ?></td>
  </tr>
</table>

<?php do_action('mngl_custom_fields'); ?>
</div>

<h4><?php _e('Mail Options', 'mingle'); ?>:</h4>
<div class="mngl-options-pane">
<span class="description"><?php _e("Configure the way mail is sent from Mingle. This setting will affect how all mail throughout WordPress is sent. If your website is hosted on Windows then you'll probably need to set this to SMTP and enter your credentials. <strong>Note:</strong> If your mail already works and you don't know what this setting means then you should probably leave it alone.", 'mingle'); ?></span>
  <p><?php MnglOptionsHelper::mailer_dropdown( 'type', $mngl_options->mailer['type'] ); ?></p>
  <p id="mngl-sendmail-form" class="mngl-options-pane mngl-hidden">
    <?php _e('Sendmail Path', 'mingle'); ?>:&nbsp;
    <?php echo MnglOptionsHelper::mailer_input( 'sendmail-path', $mngl_options->mailer['sendmail-path'], 'form-field'); ?>
  </p>
  <table id="mngl-smtp-form" class="mngl-options-pane mngl-hidden">
    <tr>
      <td><?php _e('SMTP Host', 'mingle'); ?>:&nbsp;</td>
      <td><?php echo MnglOptionsHelper::mailer_input( 'smtp-host', $mngl_options->mailer['smtp-host'], 'form-field'); ?></td>
    </tr>
    <tr>
      <td><?php _e('SMTP Port', 'mingle'); ?>:&nbsp;</td>
      <td><?php echo MnglOptionsHelper::mailer_input( 'smtp-port', $mngl_options->mailer['smtp-port'], 'form-field'); ?></td>
    </tr>
    <tr>
      <td><?php _e('SMTP Encryption', 'mingle'); ?>:&nbsp;</td>
      <td><?php echo MnglOptionsHelper::smtp_encryption_dropdown( 'smtp-secure', $mngl_options->mailer['smtp-secure'], 'form-field'); ?></td>
    </tr>
    <tr>
      <td><?php _e('SMTP Username', 'mingle'); ?>:&nbsp;</td>
      <td><?php echo MnglOptionsHelper::mailer_input( 'smtp-username', $mngl_options->mailer['smtp-username'], 'form-field'); ?></td>
    </tr>
    <tr>
      <td><?php _e('SMTP Password', 'mingle'); ?>:&nbsp;</td>
      <td><?php echo MnglOptionsHelper::mailer_input( 'smtp-password', $mngl_options->mailer['smtp-password'], 'form-field', 'password'); ?></td>
    </tr>
  </table>
</div>
<script type="text/javascript">
  mngl_mailer_options();
</script>
<p class="submit">
<input type="submit" name="Submit" value="<?php _e('Update Options', 'mingle') ?>" />
</p>

</form>

<p><a href="<?php echo str_replace( '%7E', '~', $_SERVER['REQUEST_URI']); ?>&action=add_default_friends_to_all_users"><?php _e('Add Default Friends to Existing Users', 'mingle'); ?></a></p>
</div>
