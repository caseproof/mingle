<?php
class MnglCustomFieldsController
{
  function MnglCustomFieldsController()
  {
    // These just hook back into the user porfile
    add_action('mngl-edit-user-fields', array( &$this, 'display_edit_fields' ));
    add_action('mngl-profile-update', array( &$this, 'process_custom_fields' ));

    add_action('mngl-user-signup-fields', array( &$this, 'display_signup_fields' ));
    add_action('mngl-process-signup', array( &$this, 'process_custom_fields' ));
    
    add_action('mngl-profile-info', array( &$this, 'display_info_fields'));
  }
  
  function display_edit_fields()
  {
    global $mngl_custom_field, $mngl_user;
    
    $fields = $mngl_custom_field->get_all(ARRAY_A, "visibility <> 'hidden'");
    
    require( MNGL_VIEWS_PATH . "/mngl-custom-fields/edit_fields.php" );
  }
  
  function display_signup_fields()
  {
    global $mngl_custom_field;

    $fields = $mngl_custom_field->get_all(ARRAY_A, "on_signup > 0");

    require( MNGL_VIEWS_PATH . "/mngl-custom-fields/signup_fields.php" );
  }
  
  function display_info_fields($user_id)
  {
    global $mngl_custom_field;

    $fields = $mngl_custom_field->get_all(ARRAY_A, "visibility='public'");
    
    require( MNGL_VIEWS_PATH . "/mngl-custom-fields/info_fields.php" );
  }
  
  function process_custom_fields($user_id)
  {

    global $mngl_custom_field;
    
    if(isset($_POST['mngl_custom']) and !empty($_POST['mngl_custom']))
    {
      $custom_values = $_POST['mngl_custom'];
      
      foreach( $custom_values as $field_id => $field_value )
        $mngl_custom_field->create_or_update_value($user_id, $field_id, $field_value);
    }
  }
}
?>