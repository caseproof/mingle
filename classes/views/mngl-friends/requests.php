<div class="mngl-friend-requests">
<table>
<?php
  if(count($requests) > 0)
  {
    foreach ($requests as $key => $request)
    {
      $requestor = MnglUser::get_stored_profile_by_id($request->user_id);

      if($requestor)
      {
        $avatar = $requestor->get_avatar(64);
?>
  <tr class="mngl-friend-request" id="request-<?php echo $request->id; ?>">
    <td valign="top"><?php echo $avatar; ?></td>
    <td valign="top" style="padding: 0px 0px 0px 10px; vertical-align: top;"><?php printf(__('%s wants to be your friend.', 'mingle'), '<a href="' . $requestor->get_profile_url() . '">' . $requestor->screenname . '</a>'); ?><br/><a href="javascript:mngl_accept_friend_request( '<?php echo MNGL_SCRIPT_URL; ?>', <?php echo $request->id; ?>, '<?php echo $requestor->screenname; ?>' )"><?php _e('Accept', 'mingle'); ?></a>&nbsp;|&nbsp;<a href="javascript:mngl_ignore_friend_request( '<?php echo MNGL_SCRIPT_URL; ?>', <?php echo $request->id; ?> )"><?php _e('Ignore', 'mingle'); ?></a></td>
  </tr>
<?php
      }
    }
  }
  else
  {
    ?>
      <p><?php _e("You don't currently have any friend requests.", 'mingle'); ?></p>
    <?php
  }
?>  
</table>
</div>
