<?php

class MnglOptionsController
{
  function MnglOptionsController()
  {
    add_action('mngl_custom_fields', array($this,'display_custom_fields'));
  }

  function route()
  {
    $action = (isset($_POST['action'])?$_POST['action']:$_GET['action']);
    if($action=='process-form')
      return $this->process_form();
    else if($action=='add_default_friends_to_all_users')
      $this->add_default_friends_to_all_users();
    else
      return $this->display_form();
  }

  function display_form()
  {
    global $mngl_options, $mngl_app_helper;
    
    if(MnglUser::is_logged_in_and_an_admin())
    {    
      if(!$mngl_options->setup_complete)
        require(MNGL_VIEWS_PATH . '/shared/must_configure.php');
      
      require(MNGL_VIEWS_PATH . '/mngl-options/form.php');
    }
  }

  function process_form()
  {
    global $mngl_options, $mngl_app_helper;
    
    if(MnglUser::is_logged_in_and_an_admin())
    {
      $errors = array();
      
      $errors = $mngl_options->validate($_POST,$errors);
      
      $mngl_options->update($_POST);
      
      if( count($errors) > 0 )
        require(MNGL_VIEWS_PATH . '/shared/errors.php');
      else
      {
        $mngl_options->store();
        require(MNGL_VIEWS_PATH . '/mngl-options/options_saved.php');
      }
      
      if(!$mngl_options->setup_complete)
        require(MNGL_VIEWS_PATH . '/shared/must_configure.php');
      
      require(MNGL_VIEWS_PATH . '/mngl-options/form.php');
    }
  }
  
  function display_default_friend_drop_down($default_friend='')
  {
    global $mngl_options;
    
    if(MnglUser::is_logged_in_and_an_admin())
      require(MNGL_VIEWS_PATH . '/mngl-options/default_friend.php');
  }
  
  function add_default_friends_to_all_users()
  {
    global $mngl_friends_controller;
    
    $mngl_friends_controller->add_default_friends_to_all_users();
    
    require(MNGL_VIEWS_PATH . '/mngl-options/default_friends_added.php');
      
    $this->display_form();
  }
  function display_custom_fields()
  {
    global $mngl_options, $mngl_custom_field;

    $custom_fields = $mngl_custom_field->get_all(ARRAY_A);

    require(MNGL_VIEWS_PATH . "/mngl-options/custom_fields.php");
  }
  
  function display_custom_field($index, $field=NULL, $show_add_button=true)
  {
    global $mngl_options;

    if(empty($field))
      $field = array();

    require( MNGL_VIEWS_PATH . "/mngl-options/custom_field.php");
  }
  
  function display_add_custom_field_button($index=0)
  {
    require( MNGL_VIEWS_PATH . "/mngl-options/add_custom_field_button.php");
  }
  
  function display_custom_field_options($field_index, $field_id=0)
  {
    global $mngl_options, $mngl_custom_field;

    if(isset($_POST[$mngl_options->custom_fields_str][$field_index]))
      $field = $_POST[$mngl_options->custom_fields_str][$field_index];
    else if($field_id and !empty($field_id))
      $field = $mngl_custom_field->get_field($field_id, ARRAY_A);
    else
      $field = false;

    if($field_id and !empty($field_id))
      $options = $mngl_custom_field->get_options($field_id, ARRAY_A);
    else
      $options = array();

    require(MNGL_VIEWS_PATH . "/mngl-options/custom_field_options.php");
  }
  
  function display_custom_field_option($field_index, $option_index, $option=NULL, $show_add_button=true)
  {
    global $mngl_options;

    if(empty($option))
      $option = array();

    require( MNGL_VIEWS_PATH . "/mngl-options/custom_field_option.php");
  }
  
  function display_add_custom_field_option_button($field_index, $option_index=0)
  {
    require( MNGL_VIEWS_PATH . "/mngl-options/add_custom_field_option_button.php");
  }
}
?>
