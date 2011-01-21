<?php 
  global $mngl_user;
  $status_message = $user->get_status();
  
  if(!empty($status_message) and $status_message) {
?>
<div class="mngl-profile-status">
  <?php MnglBoardsHelper::display_message( 'mngl-profile-status-message', $status_message, $false ); ?>
  <span class="mngl-time-ago mngl-board-post-second-row"><?php echo $mngl_app_helper->time_ago($user->get_status_time_ts()); ?>
  <?php if(MnglUser::is_logged_in_and_visible() and $user->id == $mngl_user->id) { ?>
    - <a href="javascript:mngl_clear_status(<?php echo $mngl_user->id; ?>);" id="mngl-clear-status-button"><?php _e("Clear", 'mingle'); ?></a>
  <?php } ?>
  </span>
</div>
<?php } ?>