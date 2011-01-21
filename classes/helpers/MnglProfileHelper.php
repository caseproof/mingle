<?php

class MnglProfileHelper
{
  function sex_dropdown($field_name, $field_value, $classes='', $tabindex='')
  { 
    if(!empty($classes))
      $classes = " {$classes}";
    
    if(!empty($tabindex))
      $tabindex = " tabindex=\"{$tabindex}\"";

    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="mngl-dropdown mngl-sex-dropdown<?php echo $classes; ?>"<?php echo $tabindex; ?>>
        <option value="">&nbsp;</option>
        <option value="female"<?php MnglAppHelper::value_is_selected($field_name, $field_value, "female"); ?>><?php _e('Female', 'mingle'); ?>&nbsp;</option>
        <option value="male"<?php MnglAppHelper::value_is_selected($field_name, $field_value, "male"); ?>><?php _e('Male', 'mingle'); ?>&nbsp;</option>
      </select>
    <?php
  }
  
  function privacy_dropdown($field_name, $field_value)
  {
    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="mngl-dropdown mngl-privacy-dropdown">
        <option value="private"<?php MnglAppHelper::value_is_selected($field_name, $field_value, "private"); ?>><?php _e('I Want My Profile to Only Be Visible To Me and My Friends', 'mingle'); ?>&nbsp;</option>
        <option value="public"<?php MnglAppHelper::value_is_selected($field_name, $field_value, "public"); ?>><?php _e('I Want My Profile to Be Visible To The World', 'mingle'); ?>&nbsp;</option>
      </select>
    <?php
  }
}
?>
