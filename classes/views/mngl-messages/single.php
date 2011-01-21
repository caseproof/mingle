<tr class="mngl_message_row">
  <td style="width: 50px;"><?php echo $avatar; ?></td>
  <td style="width: 100%;">
    <p class="mngl_message_listing"><?php echo $author->get_profile_link(); ?>&nbsp;<span class="mngl_small_gray"><?php echo date('F j \a\t g:ia', $message->created_at_ts); ?></span></p>
    <p class="mngl_message_listing"><?php echo $body ?></p>
    <?php do_action( 'mngl-message-display', $message->id ); ?>
  </td>
</tr>