<?php global $mngl_options; ?>
<?php if( $page <= 1 and !$public ) { ?>
<div id="mngl-profile-tab-control">
<ul>
  <li id="mngl-board-tab-button" class="mngl-active-profile-tab"><a href="javascript:mngl_set_active_tab('board');"><span><?php _e('Board', 'mingle'); ?></span></a></li>
  <li id="mngl-info-tab-button"><a href="javascript:mngl_set_active_tab('info');"><span><?php _e('Info', 'mingle'); ?></span></a></li>
</ul>
</div>
<?php } ?>

<div id="mngl-board-tab" class="mngl-profile-tab">
<?php

if( $page <= 1 and 
    MnglUser::is_logged_in_and_visible() and
    ( ($owner_id==$author_id) or
      $mngl_friend->is_friend($owner_id, $author_id) ) )
{
  ?>
  <div id="mngl-fake-board-post-form" class="mngl-post-form">
    <a href="javascript:mngl_show_board_post_form()"><div id="mngl-fake-board-post-input" class="mngl-board-fake-input"><?php _e("What's on your mind?", 'mingle'); ?></div></a>
  </div>
  <table id="mngl-board-post-form" class="mngl-post-form mngl-growable-hidden">
  <tr>
    <td colspan="2" id="mngl-board-post-form-cell">
      <textarea id="mngl-board-post-input" class="mngl-board-input mngl-growable"></textarea>
    </td>
  </tr>
  <tr>
    <td width="100%" style="text-align: left;"><div id="mngl-post-actions"><?php do_action('mngl-post-actions', $user->id, $mngl_user->id); ?></div></td>
    <td width="0%">
      <input type="submit" class="mngl-share-button" id="mngl-board-post-button" onclick="javascript:mngl_post_to_board( '<?php echo MNGL_SCRIPT_URL; ?>', <?php echo $owner_id; ?>, <?php echo $author_id; ?>, document.getElementById('mngl-board-post-input').value, '<?php echo (($public)?'activity':'boards'); ?>');" name="Share" value="<?php _e('Share', 'mingle'); ?>"/>
    </td>
  </table>
  <?php
}
?>
  <?php
    require_once(MNGL_MODELS_PATH . "/MnglUser.php");
    foreach ($board_posts as $board_post)
    {
      $author = MnglUser::get_stored_profile_by_id($board_post->author_id);
      $owner  = MnglUser::get_stored_profile_by_id($board_post->owner_id);
      
      if($author and $owner)
        $this->display_board_post($board_post,$public);
    }
  ?>
  <?php if( count($board_posts) >= $page_size ) { ?>
    <div id="mngl-older-posts"><a href="javascript:mngl_show_older_posts( <?php echo ($page + 1) . ",'" . (($public)?'activity':'boards') . "','" . (($public)?$mngl_user->screenname:$owner->screenname) . "'"; ?> )"><?php _e('Show Older Posts', 'mingle'); ?></a></div>
  <?php } ?>
</div>
<div id="mngl-info-tab" class="mngl-profile-tab mngl-hidden">
<table width="100%" class="profile-edit-table">
<?php if(isset($mngl_options->field_visibilities['profile_info']['name']) and isset($user->first_name) and !empty($user->first_name)) { ?>
  <tr>
    <td class="mngl-info-tab-col-1"><?php _e('First Name', 'mingle'); ?>:</td>
    <td class="mngl-info-tab-col-2"><?php echo $user->first_name; ?></td>
  </tr>
<?php } ?>
<?php if(isset($mngl_options->field_visibilities['profile_info']['name']) and isset($user->last_name) and !empty($user->last_name)) { ?>
  <tr>
    <td class="mngl-info-tab-col-1"><?php _e('Last Name', 'mingle'); ?>:</td>
    <td class="mngl-info-tab-col-2"><?php echo $user->last_name; ?></td>
  </tr>
<?php } ?>
<?php if(isset($mngl_options->field_visibilities['profile_info']['bio']) and (isset($user->bio) and !empty($user->bio))) { ?>
  <tr>
    <td class="mngl-info-tab-col-1"><?php _e('Bio', 'mingle'); ?>:</td>
    <td class="mngl-info-tab-col-2"><?php echo make_clickable(stripslashes($user->bio)); ?></td>
  </tr>
  <?php } ?>
  <?php if(isset($mngl_options->field_visibilities['profile_info']['birthday']) and (isset($user->birthday) and !empty($user->birthday))) { ?>
  <tr>
    <td class="mngl-info-tab-col-1"><?php _e('Birthday', 'mingle'); ?>:</td>
    <td class="mngl-info-tab-col-2"><?php echo $user->birthday; ?></td>
  </tr>
  <?php } ?>
  <?php if(isset($mngl_options->field_visibilities['profile_info']['url']) and (isset($user->url) and !empty($user->url))) { ?>
  <tr>
    <td class="mngl-info-tab-col-1"><?php _e('Website', 'mingle'); ?>:</td>
    <td class="mngl-info-tab-col-2"><?php echo make_clickable($user->url); ?></td>
  </tr>
  <?php } ?>
  <?php if(isset($mngl_options->field_visibilities['profile_info']['location']) and (isset($user->location) and !empty($user->location))) { ?>
  <tr>
    <td class="mngl-info-tab-col-1"><?php _e('Location', 'mingle'); ?>:</td>
    <td class="mngl-info-tab-col-2"><?php echo $user->location; ?></td>
  </tr>
  <?php } ?>
  <?php if(isset($mngl_options->field_visibilities['profile_info']['sex']) and (isset($user->sex) and !empty($user->sex))) { ?>
  <tr>
    <td class="mngl-info-tab-col-1"><?php _e('Gender', 'mingle'); ?>:</td>
    <td class="mngl-info-tab-col-2"><?php echo $user->sex_display; ?></td>
  </tr>
  <?php } ?>
  <?php do_action('mngl-profile-info', $user->id); ?>
</table>
</div>
