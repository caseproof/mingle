<div id="mngl_field_<?php echo $field_index; ?>_options" class="mngl-options-pane">
  <table class="form-table mngl-custom-fields-table" style="width: auto;">
<?php
  $option_index = 0;
  if(count($options) > 0)
  {
    foreach($options as $option)
    {
      if($option and !empty($option) and is_array($option))
        $this->display_custom_field_option($field_index, $option_index, $option, false);
      $option_index++;
    }
  }

  $this->display_add_custom_field_option_button($field_index, $option_index);
?>
  </table>
</div>