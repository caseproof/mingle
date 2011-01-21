<?php

if(isset($fields) and !empty($fields) and is_array($fields))
{
  foreach($fields as $field)
  {
    $field_value = $mngl_custom_field->get_value( $mngl_user->id, $field['id'] );

    $private = (($field['visibility'] == 'private')?"<span class=\"description\"> (".__("private", 'mingle').")</span>":'');

    ?>
      <tr>
        <td valign="top"><?php echo stripslashes($field['name']); echo $private; ?>:</td>
        <td valign="top"><?php MnglCustomFieldsHelper::custom_field($field, $field_value->value, 'mngl-profile-edit-field'); ?></td>
      </tr>
    <?php
  }
}
?>
