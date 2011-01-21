<div id="mngl-comment-form-wrap-<?php echo $board_post->id; ?>">
  <?php
  if($show_fake_form)
  {
    if(MnglUser::is_logged_in_and_visible() and 
        ( $mngl_friend->is_friend( $board_post->owner_id, $author_id ) or
          $mngl_friend->is_friend( $board_post->author_id, $author_id ) or
          ( $board_post->author_id == $author_id ) or
          ( $board_post->owner_id == $author_id ) ) )
    {
      if(count($board_post->comments) > 0)
      {
        ?>
          <div id="mngl-fake-board-comment-<?php echo $board_post->id; ?>" class="mngl-board-comments">
            <a href="javascript:mngl_toggle_comment_form('<?php echo $board_post->id; ?>')"><div class="mngl-board-fake-input"><?php _e('Write a comment.', 'mingle'); ?></div></a>
          </div>
        <?php
      }
    }
  }
  ?>
  <div id="mngl-comment-form-<?php echo $board_post_id; ?>" class="mngl-board-comments mngl-growable-hidden">
    <table class="mngl-comment-table">
     <tr>
       <td valign="top" style="width: 36px;"><?php echo $avatar; ?></a>
       </td>
       <td valign="top" class="mngl-comment-table-col-2">
         <textarea id="mngl-board-comment-input-<?php echo $board_post_id; ?>" class="mngl-board-input mngl-comment-board-input mngl-growable mngl-twolines"></textarea>
       </td>
     </tr>
    </table>
    <table class="mngl-comment-table">
      <tr>
        <td width="100%">&nbsp;</td>
        <td width="0%">
          <input type="submit" class="mngl-share-button" id="mngl-comment-button-<?php echo $board_post_id; ?>" onclick="javascript:mngl_comment_on_post( '<?php echo MNGL_SCRIPT_URL; ?>', <?php echo $author->id; ?>, <?php echo $board_post_id; ?>, document.getElementById('mngl-board-comment-input-<?php echo $board_post_id; ?>').value, '<?php echo (($public)?'activity':'boards'); ?>' )" name="Comment" value="<?php _e('Comment', 'mingle'); ?>"/>
        </td>
      </tr>
    </table>
  </div>
</div>
