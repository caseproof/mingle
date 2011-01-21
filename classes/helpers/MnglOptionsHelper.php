<?php

class MnglOptionsHelper
{
  function wp_pages_dropdown($field_name, $page_id=0, $auto_page='', $include_disabled=false)
  {
    $pages = MnglAppHelper::get_pages();
    $selected_page_id = (isset($_POST[$field_name])?$_POST[$field_name]:$page_id);
  
    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="wafp-dropdown wafp-pages-dropdown">
      <?php if($include_disabled) { ?>
        <option value=""><?php _e('- Disable Page -', 'mingle'); ?>&nbsp;</option>
      <?php } ?>
      <?php if(!empty($auto_page)) { ?>
        <option value="__auto_page:<?php echo $auto_page; ?>"><?php _e('- Auto Create New Page -', 'mingle'); ?>&nbsp;</option>
      <?php }
  
        foreach($pages as $page)
        {    
          $selected = (((isset($_POST[$field_name]) and $_POST[$field_name] == $page->ID) or (!isset($_POST[$field_name]) and $page_id == $page->ID))?' selected="selected"':'');
          ?>
          <option value="<?php echo $page->ID; ?>" <?php echo $selected; ?>><?php echo $page->post_title; ?>&nbsp;</option>
          <?php
        }
      ?>
      </select>
    <?php
  
    if($selected_page_id) {
        $permalink = get_permalink($selected_page_id);
    ?>
  &nbsp;<a href="<?php echo $permalink; ?>" target="_blank"><?php _e('View', 'mingle'); ?></a>
    <?php
    }
  }

  function profile_name_dropdown($field_name, $field_value)
  {
    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="mngl-dropdown mngl-profile-name-dropdown">
        <option value="fullname" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'fullname') or (!isset($_POST[$field_name]) and $field_value == 'fullname'))?' selected="selected"':''); ?>><?php _e('Full Name', 'mingle'); ?>&nbsp;</option>
        <option value="screenname" <?php echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'screenname') or (!isset($_POST[$field_name]) and $field_value == 'screenname'))?' selected="selected"':''); ?>><?php _e('Screen Name', 'mingle'); ?>&nbsp;</option>
      </select>
    <?php
  }
  
  function field_visibility_dropdown($field_name, $field_value)
  {
    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="mngl-dropdown mngl-field-visibility-dropdown">
        <option value="public" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'public') or (!isset($_POST[$field_name]) and $field_value == 'public'))?' selected="selected"':''); ?>><?php _e('Public', 'mingle'); ?>&nbsp;</option>
        <option value="private" <?php echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'private') or (!isset($_POST[$field_name]) and $field_value == 'private'))?' selected="selected"':''); ?>><?php _e('Private', 'mingle'); ?>&nbsp;</option>
        <option value="hidden" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'hidden') or (!isset($_POST[$field_name]) and $field_value == 'hidden'))?' selected="selected"':''); ?>><?php _e('Hidden', 'mingle'); ?>&nbsp;</option>
      </select>
    <?php
  }
  
  function users_dropdown($field_name, $user_id)
  {
    $users = MnglUtils::get_raw_users();
      
    $field_value = $_POST[$field_name];
    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="mngl-dropdown mngl-users-dropdown">
        <option>&nbsp;</option>
      <?php
        foreach($users as $user)
        {
          ?>
          <option value="<?php echo $user->ID; ?>" <?php echo (((isset($_POST[$field_name]) and $_POST[$field_name] == $user->ID) or (!isset($_POST[$field_name]) and $user_id == $user->ID))?' selected="selected"':''); ?>><?php echo $user->user_login; ?>&nbsp;(<?php echo $user->user_nicename; ?>)&nbsp;</option>
          <?php
        }
      ?>
      </select>
    <?php
  }
  
  function users_multiselect($field_name, $user_array)
  {
    $users = MnglUtils::get_raw_users();

    $field_value = $_POST[$field_name];
    
    $check_selected = (is_array($user_array) or is_array($_POST[$field_name]));
    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" class="mngl-dropdown mngl-user-multiselect" multiple="multiple">
      <?php
        foreach($users as $user)
        {
          $selected = '';

          if( ( is_array($user_array) and in_array($user->ID,$user_array) ) or 
              ( is_array($_POST[$field_name]) and in_array($user->ID,$_POST[$field_name]) ) )
            $selected = ' selected="selected"';
          ?>
          <option value="<?php echo $user->ID; ?>"<?php echo $selected ?>><?php echo $user->user_login; ?>&nbsp;(<?php echo $user->user_nicename; ?>)&nbsp;</option>
          <?php
        }
      ?>
      </select>
    <?php
  }
  
  function display_field_visibility_buttons($field_name, $custom=false, $password=false)
  {
    global $mngl_options;
    
    if($custom)
    {
      $profile_edit_fields  =& $mngl_options->field_visibilities[ 'profile_edit'  ]['custom'];
      $profile_front_fields =& $mngl_options->field_visibilities[ 'profile_front' ]['custom'];
      $profile_info_fields  =& $mngl_options->field_visibilities[ 'profile_info'  ]['custom'];
      $signup_page_fields   =& $mngl_options->field_visibilities[ 'signup_page'   ]['custom'];
    }
    else
    {
      $profile_edit_fields  =& $mngl_options->field_visibilities[ 'profile_edit'  ];
      $profile_front_fields =& $mngl_options->field_visibilities[ 'profile_front' ];
      $profile_info_fields  =& $mngl_options->field_visibilities[ 'profile_info'  ];
      $signup_page_fields   =& $mngl_options->field_visibilities[ 'signup_page'   ];
    }
    
    $password_style='';
    if($password)
      $password_style = ' style="color: #d3d3d3;"';

    MnglOptionsHelper::render_array_checkbox( $mngl_options->field_visibility_str,
                                              $profile_front_fields[$field_name],
                                              'profile_front',
                                              $field_name, 
                                              $custom,
                                              $password);
    ?><span<?php echo $password_style; ?>><?php _e('Profile Front Page', 'mingle'); ?>&nbsp;&nbsp;...</span><?php
    
    MnglOptionsHelper::render_array_checkbox( $mngl_options->field_visibility_str,
                                              $profile_info_fields[$field_name],
                                              'profile_info',
                                              $field_name,
                                              $custom,
                                              $password);
    ?><span<?php echo $password_style; ?>><?php _e('Profile Info Tab', 'mingle'); ?>&nbsp;&nbsp;...</span><?php

    MnglOptionsHelper::render_array_checkbox( $mngl_options->field_visibility_str,
                                              $profile_edit_fields[$field_name],
                                              'profile_edit',
                                              $field_name,
                                              $custom);
    ?><span><?php _e('Profile Edit Page', 'mingle'); ?>&nbsp;&nbsp;...</span><?php

    MnglOptionsHelper::render_array_checkbox( $mngl_options->field_visibility_str,
                                              $signup_page_fields[$field_name],
                                              'signup_page',
                                              $field_name,
                                              $custom );
    ?><span><?php _e('Signup Page', 'mingle'); ?></span><?php
  }

  function render_array_checkbox($field_name, $field_value, $array_type, $array_index, $custom=false, $disabled=false)
  {
    if($custom)
      $array_index_with_brackets = "[{$array_type}][custom][{$array_index}]";
    else
      $array_index_with_brackets = "[{$array_type}][{$array_index}]";

    $field_name_with_brackets = "{$field_name}{$array_index_with_brackets}";
    
    $is_checked = '';

    if( (!isset($_POST) or empty($_POST)) and
        (isset($field_value) and !empty($field_value) ) or
        ( ( !$custom and isset($_POST[$field_name][$array_type][$array_index]) ) or
          ( $custom and isset($_POST[$field_name][$array_type]['custom'][$array_index]) ) ) )
      $is_checked = ' checked="checked"';
      
    $is_disabled = '';
    
    if($disabled)
      $is_disabled = ' disabled="disabled"';

?>
    <input type="checkbox" style="width: 20px;" name="<?php echo $field_name_with_brackets; ?>" id="<?php echo $field_name_with_brackets; ?>"<?php echo $is_checked . $is_disabled; ?>/>
<?php
  }

  function mailer_dropdown($subfield, $field_value)
  {
    global $mngl_options;

    $options = array( "mail" => __('PHP Mail', 'mingle'),
                      "sendmail" => __('Sendmail', 'mingle'),
                      "smtp" => __('SMTP', 'mingle') );
    $name = "{$mngl_options->mailer_str}[{$subfield}]";
    $id   = "{$mngl_options->mailer_str}-{$subfield}";
    ?>
      <select name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="mngl-dropdown mngl-mailer-dropdown" onchange="javascript:mngl_mailer_options();">
        <?php foreach( $options as $opt_key => $opt_value ) { ?>
          <option value="<?php echo $opt_key; ?>" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == $opt_key) or (!isset($_POST[$field_name]) and $field_value == $opt_key ))?' selected="selected"':''); ?>><?php echo $opt_value; ?>&nbsp;</option>
        <?php } ?>
      </select>
    <?php
  }

  function smtp_encryption_dropdown($subfield, $field_value)
  {
    global $mngl_options;

    $options = array( "" => '',
                      "ssl" => __('SSL', 'mingle'),
                      "tls" => __('TLS', 'mingle') );
    $name = "{$mngl_options->mailer_str}[{$subfield}]";
    $id   = "{$mngl_options->mailer_str}-{$subfield}";
    ?>
      <select name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="mngl-dropdown mngl-mailer-dropdown">
        <?php foreach( $options as $opt_key => $opt_value ) { ?>
          <option value="<?php echo $opt_key; ?>" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == $opt_key) or (!isset($_POST[$field_name]) and $field_value == $opt_key ))?' selected="selected"':''); ?>><?php echo $opt_value; ?>&nbsp;</option>
        <?php } ?>
      </select>
    <?php
  }
  
  function mailer_input($subfield, $value, $classes='', $type='text')
  {
    global $mngl_options;

    $curr_val = MnglOptionsHelper::get_current_value_with_subfield($mngl_options->mailer_str, $subfield, $value);
    $name     = "{$mngl_options->mailer_str}[{$subfield}]";
    $id       = "{$mngl_options->mailer_str}-{$subfield}";
    ?>
      <input type="<?php echo $type; ?>" name="<?php echo $name; ?>" id="<?php echo $id; ?>" class="mngl-text-input <?php echo $classes; ?>" value="<?php echo stripslashes($curr_val); ?>" />
    <?php
  }
  
  function get_current_value($field, $value)
  {
    if(isset($_POST[$field]) and !empty($_POST[$field]))
      return $_POST[$field];
    else if(isset($value) and !empty($value))
      return $value;
    else if(isset($field['default_value']) and !empty($field['default_value']))
      return $field['default_value'];
    else
      return false;
  }
  
  function get_current_value_with_subfield( $field, $subfield, $value )
  {
    if(isset($_POST[$field][$subfield]) and !empty($_POST[$field][$subfield]))
      return $_POST[$field][$subfield];
    else if(isset($value) and !empty($value))
      return $value;
    else
      return false;
  }

  function field_type_dropdown($field_name, $field_value, $index)
  {
    ?>
      <select name="<?php echo $field_name; ?>" id="<?php echo $field_name; ?>" onchange="javascript:mngl_show_field_options( <?php echo $index; ?>, this.value )" class="mngl-dropdown mngl-field-type-dropdown">
        <option value="input" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'input') or (!isset($_POST[$field_name]) and $field_value == 'input'))?' selected="selected"':''); ?>><?php _e('Text Input', 'mingle'); ?>&nbsp;</option>
        <option value="textarea" <?php echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'textarea') or (!isset($_POST[$field_name]) and $field_value == 'textarea'))?' selected="selected"':''); ?>><?php _e('Text Area', 'mingle'); ?>&nbsp;</option>
        <option value="checkbox" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'checkbox') or (!isset($_POST[$field_name]) and $field_value == 'checkbox'))?' selected="selected"':''); ?>><?php _e('Checkbox', 'mingle'); ?>&nbsp;</option>
        <option value="dropdown" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'dropdown') or (!isset($_POST[$field_name]) and $field_value == 'dropdown'))?' selected="selected"':''); ?>><?php _e('Dropdown', 'mingle'); ?>&nbsp;</option>
        <option value="date" <?php  echo (((isset($_POST[$field_name]) and $_POST[$field_name] == 'date') or (!isset($_POST[$field_name]) and $field_value == 'date'))?' selected="selected"':''); ?>><?php _e('Date', 'mingle'); ?>&nbsp;</option>
      </select>
    <?php
  }
}
?>
