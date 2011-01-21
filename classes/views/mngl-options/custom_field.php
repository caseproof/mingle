<table class="form-table mngl-custom-fields-table" style="width: auto;" id="mngl-custom-field-<?php echo $index; ?>">
<tr class="form-field mngl_custom_field">
  <td><strong><?php echo ($index + 1); ?>.</strong>&nbsp;</td>
  <td>
  <input type="hidden" name="<?php echo $mngl_options->custom_fields_str . "[{$index}][id]"; ?>" value="<?php echo $field['id']; ?>" />
  <strong><?php _e('Name', 'mingle'); ?>:</strong><br/><input type="text" id="<?php echo $mngl_options->custom_fields_str . "[{$index}][name]"; ?>" name="<?php echo $mngl_options->custom_fields_str . "[{$index}][name]"; ?>" value="<?php echo stripslashes($field['name']); ?>" /></td>
  <td valign="top"><strong><?php _e('Type', 'mingle'); ?>:</strong><br/><?php echo MnglOptionsHelper::field_type_dropdown($mngl_options->custom_fields_str . "[{$index}][type]", $field['type'], $index); ?></td>
  <td valign="top"><strong><?php _e('Default Value', 'mingle'); ?>:</strong><br/><input type="text" id="<?php echo $mngl_options->custom_fields_str . "[{$index}][default_value]"; ?>" name="<?php echo $mngl_options->custom_fields_str . "[{$index}][default_value]"; ?>" value="<?php echo stripslashes($field['default_value']); ?>" /></td>
  <td valign="top"><strong><?php _e('Privacy', 'mingle'); ?>:</strong><br/>
    <?php MnglOptionsHelper::field_visibility_dropdown($mngl_options->custom_fields_str . "[{$index}][visibility]", $field['visibility']); ?>
  </td>
  <td>
    <input type="checkbox" style="width: 20px;" id="<?php echo $mngl_options->custom_fields_str . "[{$index}][on_signup]"; ?>" name="<?php echo $mngl_options->custom_fields_str . "[{$index}][on_signup]"; ?>"<?php echo ((isset($field['on_signup']) and !empty($field['on_signup']))?' checked="checked"':''); ?>/>&nbsp;<?php _e('Show on the Signup Page', 'mingle'); ?>
  </td>
  <td>
    <a href="javascript:mngl_remove_tag('#mngl-custom-field-<?php echo $index; ?>');"><img src="<?php echo MNGL_IMAGES_URL . '/remove.png'; ?>" /></a>
  </td>
</tr>
<tr id="mngl_field_options_wrapper_<?php echo $index; ?>" class="<?php echo (($field and $field['type']=='dropdown')?'':' mngl-hidden'); ?>">
  <td colspan="7">
    <?php $this->display_custom_field_options($index, $field['id']); ?>
  </td>
</tr>
</table>
<?php

if($show_add_button)
  $this->display_add_custom_field_button($index+1);
  
?>