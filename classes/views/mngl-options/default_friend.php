    <tr id="mngl-default-friend-field-<?php echo $default_friend; ?>" class="form-field">
      <td><?php _e('Default Friend', 'mingle'); ?>: </td>
      <td>
        <?php MnglOptionsHelper::users_dropdown($mngl_options->default_friends_str .'[]', $default_friend); ?>
      </td>
      <td>
        <a href="javascript:mngl_remove_tag('#mngl-default-friend-field-<?php echo $default_friend; ?>');"><img src="<?php echo MNGL_IMAGES_URL . '/remove.png'; ?>" /></a>
      </td>
    </tr>