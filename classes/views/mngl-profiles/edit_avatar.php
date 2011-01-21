<span id="mngl-avatar-edit-display"><?php echo $avatar; ?>
<?php if(!empty($mngl_user->avatar)) { ?>
  <a href="javascript:mngl_delete_profile_avatar( '<?php echo MNGL_SCRIPT_URL ?>', <?php echo $mngl_user->id; ?> )">[<?php _e('delete', 'mingle'); ?>]</a>
<?php } ?>
</span>
