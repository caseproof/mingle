<?php
  if(!$user_search)
  {
?>
<div class="mngl-friend-list-header">
  <div class="mngl-profile-image-wrap mngl-friend-list-profile-image-wrap">
    <span class="mngl-alignleft"><?php echo $user_avatar; ?></span>
    <p class="mngl-friend-list-profile-text"><?php printf(__("%s's Friends", 'mingle'), $user->screenname); ?>:</p>
  </div>
</div>
<div id="mngl-fake-search-form" class="mngl-search-form">
  <a href="javascript:mngl_show_search_form()"><div id="mngl-fake-search-input" class="mngl-board-fake-input"><?php _e("Search Friends...", 'mingle'); ?></div></a>
</div>
<div id="mngl-search-form" class="mngl-search-form mngl-hidden">
  <input type="text" id="mngl-search-input" onkeyup="javascript:mngl_search_friends( this.value, '<?php echo $page_params; ?>' )" class="mngl-search-input mngl-board-input" />
</div>
  <?php
  }
?>
<div id="mngl-friends-directory" class="friends-list">
<p><strong><?php printf( __ngettext("%s Friend Was Found", "%s Friends Were Found", $record_count, 'mingle'), number_format( (float)$record_count )); ?></strong></p>
  <?php
  if($prev_page > 0)
  {
    ?>
      <div id="mngl_prev_page"><a href="<?php echo "{$friends_page_url}{$param_char}mdp={$prev_page}{$page_params}"; ?>">&laquo; <?php _e('Previous Page', 'mingle'); ?></a></div>
    <?php
  }
  ?>
<table style="width: 100%;">
<?php
if(is_array($friends))
{
  $thumb_size = 64;
  foreach ($friends as $key => $friend)
  {
    $avatar_link = $friend->get_avatar($thumb_size);
    
    $full_name = $friend->screenname;

    if(!empty($search_query))
    {
      // highlight search term if present
      $full_name = preg_replace( "#({$search_query})#i", "<span class=\"mngl-search-match\">$1</span>", $full_name );
    }
?>
  <tr id="mngl-friend-<?php echo $friend->id; ?>">
    <td valign="top" style="width: <?php echo $thumb_size; ?>px; vertical-align: top;"><a href="<?php echo $friend->get_profile_url(); ?>"><?php echo $avatar_link; ?></a></td>
    <td valign="top" style="padding: 0px 0px 0px 10px; vertical-align: top;"><h3 style="margin: 0px;"><a href="<?php echo $friend->get_profile_url(); ?>"><?php echo "{$full_name}"; ?></a></h3><?php do_action( 'mngl-profile-list-name-display', $friend->id ); ?>
    <?php
    if($mngl_user->id == $user->id and MnglFriend::can_delete_friend($user->id, $friend->id))
    {
    ?>
      <a href="javascript:mngl_delete_friend('<?php echo MNGL_SCRIPT_URL; ?>',<?php echo $user->id; ?>,<?php echo $friend->id; ?> )"><?php _e('Delete', 'mingle'); ?></a>
      
    <?php
    do_action('mngl-friend-row', $friend, $user);
    }
    ?></td>
  </tr>
<?php
  }
}
?>  
</table>
<?php
if($next_page > 0)
{
  ?>
    <div id="mngl_prev_page"><a href="<?php echo "{$friends_page_url}{$param_char}mdp={$next_page}{$page_params}"; ?>"><?php _e('Next Page', 'mingle'); ?> &raquo;</a></div>
  <?php
}
do_action('mngl-friend-list-page');
?>
</div>
