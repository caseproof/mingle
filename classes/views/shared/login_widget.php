      <?php if (MnglUtils::is_user_logged_in()) {
              global $mngl_user, $mngl_friend, $mngl_message;
              $request_count = $mngl_friend->get_friend_requests_count( $mngl_user->id );
              $request_count_str = (($request_count > 0)?" [{$request_count}]":'');
              
              $unread_count = $mngl_message->get_unread_count();

              $unread_count_str = '';
              if($unread_count)
                $unread_count_str = " [{$unread_count}]";
        ?>
        <ul style="list-style-type: none;" class="mngl-login-widget-nav">
          <li><a href="<?php echo get_permalink($mngl_options->activity_page_id); ?>"><?php _e('Activity', 'mingle'); ?></a></li>
          <li><a href="<?php echo get_permalink($mngl_options->profile_page_id); ?>"><?php _e('Profile', 'mingle'); ?></a></li>
          <li><a href="<?php echo get_permalink($mngl_options->profile_edit_page_id); ?>"><?php _e('Settings', 'mingle'); ?></a></li>
          <li><a href="<?php echo get_permalink($mngl_options->friends_page_id); ?>"><?php _e('Friends', 'mingle'); ?></a></li>
          <li><a href="<?php echo get_permalink($mngl_options->friend_requests_page_id); ?>"><?php _e('Friend Requests', 'mingle'); ?><?php echo $request_count_str; ?></a></li>
          <li><a href="<?php echo get_permalink($mngl_options->inbox_page_id); ?>"><?php _e('Inbox', 'mingle'); ?><?php echo $unread_count_str; ?></a></li>
          <?php do_action('mngl_login_widget_pages'); ?>
          <?php
            if (!empty($mngl_options->directory_page_id)) {
              ?>
                <li><a href="<?php echo get_permalink($mngl_options->directory_page_id); ?>"><?php _e('Directory', 'mingle'); ?></a></li>
              <?php
            }
          ?>
          <li><a href="<?php echo wp_logout_url(get_permalink($mngl_options->activity_page_id)); ?>"><?php _e('Logout', 'mingle'); ?></a></li>
        </ul>
        <?php
      } else { ?>
        <p><?php printf(__('Login to connect with Others on %s', 'mingle'), $mngl_blogname); ?>:</p>
        <form name="loginform" id="loginform" action="<?php echo $login_url; ?>" method="post">
        	<p>
        		<label><strong><?php _e('Username', 'mingle'); ?></strong><br />
        		<input type="text" name="log" id="user_login" class="input" value="" tabindex="710" style="width: 100%; font-size: 12px; padding: 4px;" /></label>
        		<label><strong><?php _e('Password', 'mingle'); ?></strong><br />
        		<input type="password" name="pwd" id="user_pass" class="input" value="" tabindex="720" style="width: 100%; line-height: 12px; padding: 4px;" /></label><br/>
        	  <label><input name="rememberme" type="checkbox" id="rememberme" value="forever" tabindex="730" style="width: 15px;" /> <?php _e('Remember Me', 'mingle'); ?></label>
        	</p>
        	<p class="submit">
        		<input type="submit" name="wp-submit" id="wp-submit" class="button-primary mngl-share-button" value="<?php _e('Log In', 'mingle'); ?>" tabindex="740" />
        		<input type="hidden" name="redirect_to" value="<?php echo get_permalink($mngl_options->activity_page_id); ?>" />
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
      <?php
        if (!empty($mngl_options->directory_page_id)) {
          ?>
            <p><a href="<?php echo get_permalink($mngl_options->directory_page_id); ?>"><?php _e('Directory', 'mingle'); ?></a></p>
          <?php
        }
      ?>
<?php } ?>
        </p>
      <?php MnglAppHelper::powered_by(); ?>
