<?php

class MnglUsersController
{
  function display_login_form()
  {
    global $mngl_options, $mngl_blogurl;

    extract($_POST);
    
    $redirect_to = ( (isset($redirect_to) and !empty($redirect_to) )?$redirect_to:get_permalink( $mngl_options->activity_page_id ) );
    $redirect_to = apply_filters( 'mngl-login-redirect-url', $redirect_to );
      
    if(!empty($mngl_options->login_page_id) and $mngl_options->login_page_id > 0)
    {
      $login_url = get_permalink($mngl_options->login_page_id);
      $login_delim = MnglAppController::get_param_delimiter_char($login_url);
      $forgot_password_url = "{$login_url}{$login_delim}action=forgot_password";
    }
    else
    {
      $login_url = "{$mngl_blogurl}/wp-login.php";
      $forgot_password_url = "{$mngl_blogurl}/wp-login.php?action=lostpassword";
    }
    
    if(!empty($mngl_options->signup_page_id) and $mngl_options->signup_page_id > 0)
      $signup_url = get_permalink($mngl_options->signup_page_id);
    else
      $signup_url = $mngl_blogurl . '/wp-login.php?action=register';
    
    if(MnglUser::is_logged_in_and_visible())
      require( MNGL_VIEWS_PATH . '/shared/already_logged_in.php' );
    else
    {
      if( !empty($mngl_process_login_form) and !empty($errors) )
        require( MNGL_VIEWS_PATH . "/shared/errors.php" );

      require( MNGL_VIEWS_PATH . '/shared/login_form.php' );
    }
  }
  
  function process_login_form()
  {
    global $mngl_options, $mngl_profiles_controller;

    $errors = MnglUser::validate_login($_POST,array());
    
    $errors = apply_filters('mngl-validate-login', $errors);

    extract($_POST);
    
    if(empty($errors))
    {
      $creds = array();
      $creds['user_login'] = $log;
      $creds['user_password'] = $pwd;
      $creds['remember'] = $rememberme;

      if(!function_exists('wp_signon'))
        require_once(ABSPATH . WPINC . '/user.php');
      
      wp_signon($creds);

      $redirect_to = ((!empty($redirect_to))?$redirect_to:get_permalink($mngl_options->activity_page_id));

      MnglUtils::wp_redirect($redirect_to);
      exit;
    }
    else
      $_POST['errors'] = $errors;
  }
  
  function display_signup_form()
  {
    global $mngl_options, $mngl_blogurl;
    
    $process = MnglAppController::get_param('mngl-process-form');
    
    if(empty($process))
    {
      if(MnglUser::is_logged_in_and_visible())
        require( MNGL_VIEWS_PATH . '/shared/already_logged_in.php' );
      else
        require( MNGL_VIEWS_PATH . '/shared/signup_form.php' );
    }
    else
      $this->process_signup_form();
  }
  
  function process_signup_form()
  {
    global $mngl_options;

    $errors = MnglUser::validate_signup($_POST,array());
    
    $errors = apply_filters('mngl-validate-signup', $errors);
    
    extract($_POST);
    
    if(empty($errors))
    {
      if(isset($mngl_options->field_visibilities['signup_page']['password']))
        $new_password = $mngl_user_password;
      else
        $new_password = apply_filters('mngl-create-signup-password', MnglUtils::wp_generate_password( 12, false ));

      $user_id = wp_create_user( $user_login, $new_password, $user_email );
      $user = MnglUser::get_stored_profile_by_id($user_id);
      
      if($user)
      { 
        if(isset($mngl_options->field_visibilities['signup_page']['name']))
        {
          if(isset($user_first_name) and !empty($user_first_name))
            $user->first_name = $user_first_name;
          
          if(isset($user_last_name) and !empty($user_last_name))
            $user->last_name = $user_last_name;
        }
          
        if(isset($mngl_options->field_visibilities['signup_page']['sex']))
          $user->sex = $mngl_user_sex;
        
        if(isset($mngl_options->field_visibilities['signup_page']['url']))
          $user->url = $mngl_user_url;

        if(isset($mngl_options->field_visibilities['signup_page']['location']))
          $user->location = $mngl_user_location;

        if(isset($mngl_options->field_visibilities['signup_page']['bio']))
          $user->bio = $mngl_user_bio;
        
        $user->store(true);
        
        $user->send_account_notifications($new_password);
        
        do_action('mngl-process-signup',$user_id);
        
        global $mngl_blogname;
        require( MNGL_VIEWS_PATH . "/mngl-users/signup_thankyou.php" );
      }
      else
        require( MNGL_VIEWS_PATH . "/shared/unknown_error.php" );
    }
    else
    {
      require( MNGL_VIEWS_PATH . "/shared/errors.php" );
      require( MNGL_VIEWS_PATH . '/shared/signup_form.php' );
    }
  }
  
  function display_forgot_password_form()
  {
    global $mngl_options, $mngl_blogurl;
    
    $process = MnglAppController::get_param('mngl_process_forgot_password_form');
    
    if(empty($process))
      require( MNGL_VIEWS_PATH . '/mngl-users/forgot_password.php' );
    else
      $this->process_forgot_password_form();
  }
  
  function process_forgot_password_form()
  {
    global $mngl_options;

    $errors = MnglUser::validate_forgot_password($_POST,array());
    
    extract($_POST);
    
    if(empty($errors))
    {
      $is_email = (is_email($mngl_user_or_email) and email_exists($mngl_user_or_email));
      
      if(!function_exists('username_exists'))
        require_once(ABSPATH . WPINC . '/registration.php');

      $is_username = username_exists($mngl_user_or_email);
      
      $user = false;

      // If the username & email are identical then let's rely on it as a username first and foremost
      if($is_username)
        $user = MnglUser::get_stored_profile_by_screenname( $mngl_user_or_email );
      else if($is_email)
        $user = MnglUser::get_stored_profile_by_id( MnglUtils::get_user_id_by_email( $mngl_user_or_email ) );
      
      if($user)
      {
        $user->send_reset_password_requested_notification();

        require( MNGL_VIEWS_PATH . "/mngl-users/forgot_password_requested.php" );
      }
      else
        require( MNGL_VIEWS_PATH . "/shared/unknown_error.php" );
    }
    else
    {
      require( MNGL_VIEWS_PATH . "/shared/errors.php" );
      require( MNGL_VIEWS_PATH . '/mngl-users/forgot_password.php' );
    }
  }
  
  function display_reset_password_form($mngl_key,$mngl_screenname)
  {
    $user = MnglUser::get_stored_profile_by_screenname($mngl_screenname);

    if($user)
    {
      if($user->reset_form_key_is_valid($mngl_key))
        require( MNGL_VIEWS_PATH . '/mngl-users/reset_password.php' );
      else
        require( MNGL_VIEWS_PATH . '/shared/unauthorized.php' );
    }
    else
      require( MNGL_VIEWS_PATH . '/shared/unauthorized.php' );
  }
  
  function process_reset_password_form()
  {
    global $mngl_options;
    $errors = MnglUser::validate_reset_password($_POST,array());
    
    extract($_POST);
    
    if(empty($errors))
    {
      $user = MnglUser::get_stored_profile_by_screenname( $mngl_screenname );
      
      if($user)
      {
        $user->set_password_and_send_notifications($mngl_key, $mngl_user_password);

        require( MNGL_VIEWS_PATH . "/mngl-users/reset_password_thankyou.php" );
      }
      else
        require( MNGL_VIEWS_PATH . "/shared/unknown_error.php" );
    }
    else
    {
      require( MNGL_VIEWS_PATH . "/shared/errors.php" );
      require( MNGL_VIEWS_PATH . '/mngl-users/reset_password.php' );
    }
  }
}
?>
