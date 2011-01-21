<?php
class MnglCustomFieldsHelper
{
  function custom_field($field,$field_value, $classes='')
  {
    switch( $field['type'] )
    {
      case 'input':
        MnglCustomFieldsHelper::text_input($field,$field_value,$classes);
        break;
      case 'textarea':
        MnglCustomFieldsHelper::text_area($field,$field_value,$classes);
        break;
      case 'dropdown':
        MnglCustomFieldsHelper::dropdown($field,$field_value,$classes);
        break;  
      case 'date':
        MnglCustomFieldsHelper::date_input($field,$field_value,$classes);
        break; 
      case 'checkbox':
        MnglCustomFieldsHelper::checkbox($field,$field_value,$classes);
        break;
    }
  }
  
  function text_input($field, $value, $classes='')
  {
    $curr_val = MnglCustomFieldsHelper::get_current_value($field, $value);
    ?>
      <input type="text" name="mngl_custom[<?php echo $field['id']; ?>]" id="mngl_custom[<?php echo $field['id']; ?>]" class="mngl-text-input <?php echo $classes; ?>" value="<?php echo stripslashes($curr_val); ?>" />
    <?php
  }
  
  function text_area($field, $value, $classes='')
  {  
    $curr_val = MnglCustomFieldsHelper::get_current_value($field, $value);
    ?>
      <textarea name="mngl_custom[<?php echo $field['id']; ?>]" id="mngl_custom[<?php echo $field['id']; ?>]" class="mngl-textarea mngl-growable <?php echo $classes; ?>"><?php echo stripslashes($curr_val); ?></textarea>
    <?php
  }
  
  function dropdown($field, $value, $classes='')
  {
    global $mngl_custom_field;

    $options = $mngl_custom_field->get_options( $field['id'], ARRAY_A );

    $curr_val = MnglCustomFieldsHelper::get_current_value($field, $value);
    
    ?>
      <select name="mngl_custom[<?php echo $field['id']; ?>]" id="mngl_custom[<?php echo $field['id']; ?>]" class="mngl-dropdown mngl-custom-dropdown">
    <?php
        if(!isset($field['default_value']) or empty($field['default_value']))
        {
          ?>
          <option>&nbsp;</option>
          <?php
        }

        if(isset($options) and !empty($options) and is_array($options))
        {
          foreach($options as $option)
          {
            if($curr_val and ($curr_val == $option['value']))
              $selected = ' selected="selected"';
            else
              $selected = '';
          
            ?>
              <option value="<?php echo $option['value']; ?>"<?php echo $selected; ?>><?php echo stripslashes($option['label']); ?>&nbsp;</option>
            <?php
          }
        }
    ?>
      </select>
    <?php    
  }
  
  function date_input($field, $value, $classes='')
  {
    $curr_val = MnglCustomFieldsHelper::get_current_value($field, $value);
    ?>
      <input type="text" name="mngl_custom[<?php echo $field['id']; ?>]" id="mngl_custom[<?php echo $field['id']; ?>]" class="mngl-text-input mngl-datepicker <?php echo $classes; ?>" value="<?php echo $curr_val; ?>" />
    <?php 
  }
  
  function checkbox($field, $value, $classes='')
  {
    $curr_val = MnglCustomFieldsHelper::get_current_value($field, $value);
    
    if(isset($curr_val) and !empty($curr_val) and $curr_val)
      $checked = ' checked="checked"';
    else
      $checked = '';

    ?>
      <input type="checkbox" name="mngl_custom[<?php echo $field['id']; ?>]" id="mngl_custom[<?php echo $field['id']; ?>]" class="mngl-checkbox <?php echo $classes; ?>"<?php echo $checked; ?> />
    <?php
  }
  
  function get_current_value($field, $value)
  {
    if(isset($_POST['mngl_custom'][$field['id']]) and !empty($_POST['mngl_custom'][$field['id']]))
      return $_POST['mngl_custom'][$field['id']];
    else if(isset($value) and !empty($value))
      return $value;
    else if(isset($field['default_value']) and !empty($field['default_value']))
      return $field['default_value'];
    else
      return false;
  }
}
?>