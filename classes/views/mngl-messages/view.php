<p><a href="<?php echo $mngl_message->get_messages_url(); ?>">&laquo;&nbsp;<?php _e("Back to Messages", 'mingle'); ?></a></p>
<h3><?php echo MnglAppHelper::format_text($thread->subject); ?></h3>
<p><?php echo MnglMessagesHelper::format_party_list(explode(',',$thread->parties)); ?></p>

<table cellspacing="0" cellpadding="0" id="mngl_messages_table">
<?php

if(is_array($messages) and !empty($messages))
{
  foreach($messages as $message)
    $this->display_single_message($message);
}
else
{
?>
  <tr><td><?php _e('No Messages Were Found','mingle'); ?></td></tr>
<?php
}
?>
</table>
<table width="100%" class="mngl_form_table">
  <tr>
    <td valign="top" style="width: 60px;"><?php _e('Reply', 'mingle'); ?>:</td>
    <td valign="top"><textarea name="mngl_reply" id="mngl_reply" class="mngl-profile-edit-field mngl-growable"></textarea></td>
  </tr>
</table>
<div style="text-align: right;">
  <input type="submit" class="mngl-share-button" id="mngl_reply_button" onclick="javascript:mngl_reply_to_message( <?php echo $thread_id; ?>, document.getElementById('mngl_reply').value )" name="Reply" value="<?php _e('Reply', 'mingle'); ?>"/><img id="mngl_reply_loading" class="mngl-hidden" src="<?php echo MNGL_IMAGES_URL; ?>/ajax-loader.gif" alt="<?php _e('Loading...', 'mingle'); ?>" />
</div>
