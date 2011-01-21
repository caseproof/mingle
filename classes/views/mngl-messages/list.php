<p><a href="javascript:mngl_toggle_message_composer()" id="mngl_message_composer_button"><?php _e('Compose a Message', 'mingle'); ?></a>
<?php $this->display_composer(); ?>
</p>
<p>
  <select id="mngl_message_actions" name="mngl_message_actions">
    <option>&nbsp;</option>
    <option value="mark_read"><?php _e('Mark as Read', 'mingle'); ?></option>
    <option value="mark_unread"><?php _e('Mark as Unread', 'mingle'); ?></option>
    <option value="delete_threads"><?php _e('Delete', 'mingle'); ?></option>
  </select>
  <input type="submit" class="mngl-share-button" style="height: 24px; padding: 0px 2px 24px 2px !important;" value="<?php _e('Apply', 'mingle'); ?>" name="mngl_message_action_button" id="mngl_message_action_button" onclick="javascript:mngl_bulk_action()" />
</p>
<?php
if($prev_page > 0)
{
  ?>
    <div id="mngl_prev_page" style="display: inline;"><a href="<?php echo "{$permalink}{$param_char}mgp={$prev_page}"; ?>">&laquo; <?php _e('Previous Page', 'mingle'); ?></a></div>
  <?php
}

if($next_page > 0)
{
  ?>
    <div id="mngl_next_page" style="float: right;"><a href="<?php echo "{$permalink}{$param_char}mgp={$next_page}"; ?>"><?php _e('Next Page', 'mingle'); ?> &raquo;</a></div>
  <?php
}
?>
<table cellspacing="0" cellpadding="0" class="mngl_messages_table">
<?php

if(is_array($messages) and !empty($messages))
{
  foreach($messages as $message)
  {
    $author = MnglUser::get_stored_profile_by_id($message['latest']->author_id);
    $avatar = $author->get_avatar(48);
  
    $body = strip_tags(MnglBoardsHelper::format_message($message['latest']->body, true));

    $body_excerpt = substr($body, 0, 50);
    
    $body_excerpt .= ((strcmp($body,$body_excerpt) > 0)?"...":'');

  ?>
    <tr id="mngl_thread_<?php echo $message['thread']->id; ?>" class="mngl_message_row<?php echo (($message['latest']->unread)?" mngl_message_unread":''); ?>">
      <td style="width: 30px; padding-right: 0px; margin-right: 0px;"><input type="checkbox" name="mngl_message_checkbox" class="mngl_message_checkbox" value="<?php echo $message['thread']->id; ?>" /></td>
      <td style="width: 50px;"><?php echo $avatar; ?></td>
      <td style="width: 120px;"><p class="mngl_message_listing"><a href="<?php echo $author->get_profile_url(); ?>"><?php echo "{$author->screenname}"; ?></a><br/><span class="mngl_small_gray"><?php echo date('F j \a\t g:ia', $message['latest']->created_at_ts); ?></span></p>
      </td>
      <td><p class="mngl_message_listing"><strong><a href="<?php echo $mngl_message->get_message_url( $message['thread']->id ); ?>"><?php echo MnglAppHelper::format_text($message['thread']->subject); ?></a></strong><br/><span class="mngl_small_gray"><?php echo $body_excerpt; ?></span></p>
      </td>
      <td style="width: 20px;">
        <a href="javascript:mngl_delete_thread(<?php echo $message['thread']->id; ?>);"><img src="<?php echo MNGL_IMAGES_URL . '/remove.png'; ?>" /></a>
      </td>
    </tr>
  <?php
  }
}
else
{
?>
  <tr><td><?php _e('No Messages Were Found','mingle'); ?></td></tr>
<?php
}
?>
</table>
<?php
if($prev_page > 0)
{
  ?>
    <div id="mngl_prev_page" style="display: inline;"><a href="<?php echo "{$permalink}{$param_char}mgp={$prev_page}"; ?>">&laquo; <?php _e('Previous Page', 'mingle'); ?></a></div>
  <?php
}

if($next_page > 0)
{
  ?>
    <div id="mngl_next_page" style="float: right;"><a href="<?php echo "{$permalink}{$param_char}mgp={$next_page}"; ?>"><?php _e('Next Page', 'mingle'); ?> &raquo;</a></div>
  <?php
}
?>