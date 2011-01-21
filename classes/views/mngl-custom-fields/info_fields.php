<?php

if(isset($fields) and !empty($fields) and is_array($fields))
{
  foreach($fields as $field)
  {
    $field_value = $mngl_custom_field->get_value( $user_id, $field['id'] );

    ?>
      <tr>
        <td class="mngl-info-tab-col-1"><?php echo stripslashes($field['name']); ?>:</td>
        <td class="mngl-info-tab-col-2"><?php echo stripslashes($field_value->value); ?></td>
      </tr>
    <?php
  }
}
?>
