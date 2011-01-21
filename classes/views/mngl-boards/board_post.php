<?php

$owner_profile_url   = $owner->get_profile_url();
$author_profile_url  = $author->get_profile_url();
$author_profile_link = "<a href=\"{$author_profile_url}\">{$author->screenname}</a>";
$owner_profile_link  = "<a href=\"{$owner_profile_url}\">{$owner->screenname}</a>";

$posted_on = '';
if($public)
  $posted_on = sprintf(__("Posted on %s's Board","mingle"),$owner_profile_link) . " ";

if(isset($_GET['mbpost']))
{
  $owner_avatar = $owner->get_avatar(75);
?>
    <div style="float: left;"><?php echo $owner_avatar; ?></div>
    <p class="mngl-friend-list-profile-text" style="height: 75px;"><?php printf(__("Post from %s's Board:", 'mingle'), $owner_profile_link); ?></p>
    <hr style="margin-top: 10px;"/>
<?php
}

if($board_post->type == 'post')
{
?>
  <table class="mngl-board-post-<?php echo $board_post->id; ?> mngl-board-post mngl-board-post-post">
    <tr>
      <td valign="top" style="width: 48px;"><?php echo $author->get_avatar(48); ?></td>
      <td valign="top" style="width: 100%;" class="mngl-valign-top">
        <div class="mngl-board-post-message">
          <?php echo $author_profile_link; ?> <?php MnglBoardsHelper::display_message('mngl-board-post-message-'.$board_post->id, $board_post->message); ?><br/><?php do_action( 'mngl-board-post-message-display', $board_post->id ); ?>
          <span class="mngl-board-post-second-row">
          <?php echo $posted_on; ?><span class="mngl-time-ago"><?php echo $mngl_app_helper->time_ago($board_post->created_at_ts); ?></span><?php 
          if(MnglUser::is_logged_in_and_visible() and ( $mngl_friend->is_friend( $board_post->owner_id, $author_id ) or $mngl_friend->is_self( $board_post->owner_id, $author_id ) ) )
          {
            ?> - <a href="javascript:mngl_toggle_comment_form('<?php echo $board_post->id; ?>')"><?php _e('Comment', 'mingle'); ?></a><?php
          }
          
          ?> - <a href="<?php echo MnglBoardsHelper::board_post_url($board_post->id); ?>"><?php _e('View', 'mingle'); ?></a><?php
          
          if(((($mngl_user->id == $board_post->owner_id) or ($mngl_user->id == $board_post->author_id)) and !$public) or current_user_can('level_10'))
          {
            ?> - <a href="javascript:mngl_delete_board_post( '<?php echo MNGL_SCRIPT_URL; ?>', <?php echo $board_post->id; ?>, '<?php echo (($public)?'activity':'boards'); ?>' )"><?php _e('Delete', 'mingle'); ?></a><?php
          }
          ?></span>
        </div>
      </td>
    </tr>
  </table>
  <?php do_action( 'mngl-board-post-display', $board_post->id ); ?>
<?php
}
else if($board_post->type == 'activity')
{ 
  
  $message_str = $mngl_options->activity_types[$board_post->source]['message']; //$board_post->message;
  
  $message_parts  = explode('|', $message_str);
  $message_format = preg_replace("#'#", "\\'", $message_parts[0]);
  $message_vars   = ((empty($message_parts[1]))?'':', '.$message_parts[1]);
  
  $message = 'sprintf(\''.$message_format.'\'' . $message_vars . ')';
  
  $vars = unserialize($board_post->message); // in an activity call the message is the serialized vars array
  
  $eval_code = '$message = '.$message.';';

  // Replace fields
  eval( $eval_code );

?>
  <div class="mngl-board-post-<?php echo $board_post->id; ?> mngl-board-post mngl-board-activity">
    <img class="mngl-profile-image mngl-board-activity-image" src="<?php echo $mngl_options->activity_types[$board_post->source]['icon']; ?>" />&nbsp;<?php echo stripslashes($message); ?> - <span class="mngl-board-post-second-row"><span class="mngl-time-ago"><?php echo $mngl_app_helper->time_ago($board_post->created_at_ts); ?></span><?php
    if(MnglUser::is_logged_in_and_visible() and ( $mngl_friend->is_friend( $board_post->owner_id, $author_id ) or $mngl_friend->is_self( $board_post->owner_id, $author_id ) ) )
    {
    ?> - <a href="javascript:mngl_toggle_comment_form('<?php echo $board_post->id; ?>')"><?php _e('Comment', 'mingle'); ?></a><?php
    }
    ?> - <a href="<?php echo MnglBoardsHelper::board_post_url($board_post->id); ?>"><?php _e('View', 'mingle'); ?></a><?php
    if((($mngl_user->id == $board_post->owner_id) and !$public) or current_user_can('level_10'))
    {
      ?> - <a href="javascript:mngl_delete_board_post( '<?php echo MNGL_SCRIPT_URL; ?>', <?php echo $board_post->id; ?>, '<?php echo (($public)?'activity':'boards'); ?>' )"><?php _e('Delete', 'mingle'); ?></a><?php
    }
    ?></span>
  </div>
<?php
}

?>
    <div id="mngl-board-comment-list-<?php echo $board_post->id; ?>" class="mngl-board-comment-list<?php echo ((count($board_post->comments) >= 1)?"":" mngl-growable-hidden"); ?>">
    <?php
      if(count($board_post->comments) > 3)
      {
        ?>
          <a href="javascript:mngl_toggle_hidden_comments('<?php echo $board_post->id; ?>')">
            <div id="mngl-show-hidden-comments-<?php echo $board_post->id; ?>" class="mngl-board-comments">&nbsp;<?php printf(__('Show all %d comments', 'mingle'), count($board_post->comments)); ?></div>
          </a>
        <?php
      }

      foreach ($board_post->comments as $index => $comment)
      {
        $comment_hidden_class = (($index < (count($board_post->comments) - 3))?" mngl-hidden mngl-hidden-comment-{$board_post->id}":'');
        $mngl_boards_controller->display_comment($comment, $public, $comment_hidden_class);
      }
      
      $this->display_comment_form( $author_id, $board_post, $public, true );
      ?>
    </div>
