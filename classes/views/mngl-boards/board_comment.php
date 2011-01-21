<?php
$comment_author = MnglUser::get_stored_profile_by_id($comment->author_id);

if($comment_author)
{
?>
  <div id="mngl-board-comment-<?php echo $comment->id; ?>" class="mngl-real-board-comment mngl-board-comments<?php echo $comment_hidden_class; ?>">
    <table width="100%" class="mngl-comment-table">
      <tr>
        <td valign="top" width="38px" class="mngl-valign-top"><?php echo $comment_author->get_avatar(36); ?></td>
        <td valign="top" class="mngl-valign-top">
          <div class="mngl-board-comment-message">
            <a href="<?php echo $comment_author->get_profile_url(); ?>"><?php echo "{$comment_author->screenname}"; ?></a> <?php MnglBoardsHelper::display_message('mngl-board-comment-message-'.$comment->id, $comment->message, false); ?><br/>
            <span class="mngl-board-post-second-row"><span class="mngl-time-ago"><?php echo $mngl_app_helper->time_ago($comment->created_at_ts); ?></span><?php
            if($mngl_user and (($mngl_user->id == $board_post->owner_id) or ($mngl_user->id == $comment->author_id) or current_user_can('level_10')))
            {
              ?> - <a href="javascript:mngl_delete_board_comment( '<?php echo MNGL_SCRIPT_URL; ?>', <?php echo $comment->id; ?>, '<?php echo (($public)?'activity':'boards'); ?>' )"><?php _e('Delete', 'mingle'); ?></a><?php
            }
            ?>
            </span>
          </div>
        </td>
      </tr>
    </table>
  </div>
<?php
}
?>
