<?php

class MnglAppController
{
  function MnglAppController()
  {
    add_filter('mngl-show-powered-by', array(&$this, 'show_powered_by'));
    add_filter('the_content', array( &$this, 'page_route' ), 100);
    add_action('wp_enqueue_scripts', array(&$this, 'load_scripts'), 1);
    add_action('admin_enqueue_scripts', array(&$this,'load_admin_scripts'));
    register_activation_hook(MNGL_PATH."/mingle.php", array( &$this, 'install' ));
    
    // Used to process standalone requests (make sure mingle_init comes before parse_standalone_request)
    add_action('init', array(&$this,'mingle_init'));
    add_action('init', array(&$this,'parse_standalone_request'));
    add_filter('request', array(&$this,'parse_pretty_profile_url'));
    
    add_action('phpmailer_init', array(&$this, 'set_wp_mail_return_path'));
    add_action('phpmailer_init', array(&$this, 'set_wp_mailer'));
    add_action('admin_init', array(&$this, 'prevent_admin_access'));
  }
  
  function show_powered_by($value)
  {
    global $mngl_options;
    return $mngl_options->show_powered_by;
  }

  function setup_menus()
  {
    add_action('admin_menu', array( &$this, 'menu' ));
  }
  
  /********* INSTALL PLUGIN ***********/
  function install()
  {
    global $mngl_db, $mngl_update;
    
    $mngl_db->upgrade();
  }
  
  function menu()
  {
    global $mngl_options_controller, $mngl_update;
  
    add_menu_page(__('Mingle', 'mingle'), __('Mingle', 'mingle'), 8, 'mingle-options', array(&$mngl_options_controller,'route'), MNGL_URL . "/images/mingle_16.png");
    add_submenu_page( 'mingle-options', __('Options', 'mingle'), __('Options', 'mingle'), 8, 'mingle-options', array(&$mngl_options_controller,'route') );
  }
  
  function prevent_admin_access()
  {
    global $mngl_options;
    
    // Only prevent subscribers from accessing admin pages...
    if( $mngl_options->prevent_admin_access and 
        current_user_can('level_0') and 
        !current_user_can('level_1') )
    {
      $activity_page = get_permalink($mngl_options->activity_page_id);
      die("<script type='text/javascript'>window.location='{$activity_page}' </script>");
    }
  }
  
  // Routes for wordpress pages -- we're just replacing content here folks.
  function page_route($content)
  {
    global $post, 
           $mngl_options, 
           $mngl_profiles_controller, 
           $mngl_boards_controller,
           $mngl_friends_controller,
           $mngl_messages_controller, 
           $mngl_users_controller;

    $mngl_board_post =& MnglBoardPost::get_stored_object();

    switch( $post->ID )
    {  
      case $mngl_options->activity_page_id:
        // Start output buffering -- we want to return the output as a string
        ob_start();
        $mngl_profiles_controller->activity();
        // Pull all the output into this variable
        $content = ob_get_contents();
        // End and erase the output buffer (so we control where it's output)
        ob_end_clean();
        break;
      case $mngl_options->profile_page_id:
        ob_start();
        if($this->get_param('mbpost'))
          $mngl_boards_controller->display_board_post($mngl_board_post->get_one($this->get_param('mbpost'),true));
        else
          $mngl_profiles_controller->profile($this->get_param('mu'));
        $content = ob_get_contents();
        ob_end_clean();
        break;
      case $mngl_options->directory_page_id:
        ob_start();
        $mngl_profiles_controller->directory($this->get_param('mdp'),false,$this->get_param('sq'));
        $content = ob_get_contents();
        ob_end_clean();
        break;
      case $mngl_options->profile_edit_page_id:
        ob_start();
        $mngl_profiles_controller->edit();
        $content = ob_get_contents();
        ob_end_clean();
        break;
      case $mngl_options->friends_page_id:
        ob_start();
        $mngl_friends_controller->list_friends($this->get_param('mdp'), $this->get_param('mu'));
        $content = ob_get_contents();
        ob_end_clean();
        break;
      case $mngl_options->friend_requests_page_id:
        ob_start();
        $mngl_friends_controller->list_friend_requests();
        $content = ob_get_contents();
        ob_end_clean();
        break;
      case $mngl_options->login_page_id:
        ob_start();
        $action = $this->get_param('action');

        if( $action and $action == 'forgot_password' )
          $mngl_users_controller->display_forgot_password_form();
        else if( $action and $action == 'mngl_process_forgot_password' )
          $mngl_users_controller->process_forgot_password_form();
        else if( $action and $action == 'reset_password')
          $mngl_users_controller->display_reset_password_form($this->get_param('mkey'),$this->get_param('mu'));
        else if( $action and $action == 'mngl_process_reset_password_form')
          $mngl_users_controller->process_reset_password_form();
        else
          $mngl_users_controller->display_login_form();

        $content = ob_get_contents();
        ob_end_clean();
        break;
      case $mngl_options->signup_page_id:
        ob_start();
        $mngl_users_controller->display_signup_form();
        $content = ob_get_contents();
        ob_end_clean();
        break;
      case $mngl_options->inbox_page_id:
        // Start output buffering -- we want to return the output as a string
        ob_start();
        $thread_id = $this->get_param('t');
        $action    = $this->get_param('action');
      
        if( isset($action) and 
            ($action == 'view') and
            isset($thread_id) )
          $mngl_messages_controller->display_message( $thread_id );
        else if( isset($action) and 
                 $action == 'mngl_process_composer_form')
          $mngl_messages_controller->create_message( $this->get_param('mngl_user_id'), 
                                                     $this->get_param('mngl_message_subject'),
                                                     $this->get_param('mngl_message_body'),
                                                     $this->get_param('mngl_message_recipients') );
        else
          $mngl_messages_controller->display_messages($this->get_param('mgp'));
      
        // Pull all the output into this variable
        $content = ob_get_contents();
        // End and erase the output buffer (so we control where it's output)
        ob_end_clean();
        break;
    }
    
    return $content;
  }  

  function load_scripts()
  {
    $this->enqueue_mngl_scripts();
  }
  
  function load_admin_scripts()
  {
    $admin_pages = apply_filters('mngl_admin_pages',array('mingle-options'));
    
    $curr_page = $_GET['page'];

    if(in_array($curr_page,$admin_pages))
      $this->enqueue_mngl_scripts();
  }
  
  function enqueue_mngl_scripts()
  {
    global $mngl_blogurl;

    $mngl_js = $mngl_blogurl . '/index.php?mingle_js=mingle';

    if(MnglUtils::is_version_at_least( '3.0-beta2' ))
      wp_enqueue_style( 'jquery-ui-all', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css' );
    else
      wp_enqueue_style( 'jquery-ui-all', MNGL_CSS_URL . '/jquery/ui.all.css' );
        
    wp_enqueue_style( 'mingle',  MNGL_CSS_URL . '/mingle.css' );

    wp_enqueue_script( 'jquery-elastic', MNGL_JS_URL . '/jquery.elastic.js', array('jquery') );
    wp_enqueue_script( 'jquery-qtip', MNGL_JS_URL . '/jquery.qtip-1.0.0-rc3.min.js', array('jquery') );
    
    if(MnglUtils::is_version_at_least( '3.0-beta2' ))
    {
      wp_enqueue_script( 'jquery-ui', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/jquery-ui.min.js', array('jquery') );
      wp_enqueue_script( 'mingle', $mngl_js, array('jquery','jquery-elastic','jquery-qtip','jquery-ui') );
    }
    else
    { // load older javascript libraries
      wp_enqueue_script( 'jquery-new-ui-core',    MNGL_JS_URL . '/ui.core.js', array('jquery') );
      wp_enqueue_script( 'jquery-ui-datepicker', MNGL_JS_URL . '/ui.datepicker.js', array('jquery','jquery-new-ui-core') );
      wp_enqueue_script( 'mingle', $mngl_js, array('jquery','jquery-elastic','jquery-qtip','jquery-new-ui-core','jquery-ui-datepicker') );
    }



    do_action('mngl_enqueue_scripts');
  }
  
  function mingle_js()
  {
    header('Content-type: application/javascript');
    require_once( MNGL_JS_PATH . '/mingle.js.php' );
  }

  // The tight way to process standalone requests dogg...
  function parse_standalone_request()
  {
    global $mngl_users_controller;

    $plugin     = $this->get_param('plugin');
    $action     = $this->get_param('action');
    $controller = $this->get_param('controller');
    $mingle_js  = $this->get_param('mingle_js');

    if( !empty($plugin) and $plugin == 'mingle' and 
        !empty($controller) and !empty($action) )
    {
      $this->standalone_route($controller, $action);
      exit;
    }
    else if( !empty($mingle_js) )
    {
      $this->standalone_route('js', $mingle_js);
      exit;
    }
    else if( isset( $_POST ) and isset( $_POST['mngl_process_login_form'] ) )
      $mngl_users_controller->process_login_form();
  }
  
  function parse_pretty_profile_url($query_vars)
  {
    global $mngl_blogurl, $mngl_options, $wpdb;
    
    if( MnglUtils::rewriting_on() and $mngl_options->pretty_profile_urls )
    {
      $request_uri = urldecode($_SERVER['REQUEST_URI']);
      
      // Resolve WP installs in sub-directories
      preg_match('#^https?://.*?(/.*)$#', $mngl_blogurl, $subdir);
      
      $struct = MnglUtils::get_permalink_pre_slug_uri();
      
      $match_str = '#^'.$subdir[1].$struct.'(.*?)([\?/].*?)?$#';

      if(preg_match($match_str, $request_uri, $match_val))
      {
        // match short slugs (most common)
        if(isset($match_val[1]) and !empty($match_val[1]) and MnglUser::screenname_exists_and_visible($match_val[1]))
        {
          // figure out the pagename var
          $pagename = get_permalink($mngl_options->profile_page_id);
          $pagename = str_replace( $mngl_blogurl . $struct, '', $pagename);
          $pagename = preg_replace( '#^/#', '', $pagename);
          $pagename = preg_replace( '#/$#', '', $pagename);

          // Resolve the pagename to the profile page
          $query_vars['pagename'] = $pagename;

          // Artificially set the GET variable
          $_REQUEST['mu'] = $match_val[1];

          // Unset the indeterminate query_var['name'] now that we have a pagename
          unset($query_vars['name']);
        }
      }  
    }
    
    return $query_vars;
  }

  // Routes for standalone / ajax requests
  function standalone_route($controller, $action)
  {
    global $mngl_friends_controller, 
           $mngl_boards_controller,
           $mngl_profiles_controller,
           $mngl_messages_controller,
           $mngl_captcha_controller,
           $mngl_options_controller;
    
    if($controller=='friends')
    {
      if($action=='friend_request')
        $mngl_friends_controller->friend_request($this->get_param('user_id'), $this->get_param('friend_id'));
      if($action=='delete_friend')
        $mngl_friends_controller->delete_friend($this->get_param('user_id'), $this->get_param('friend_id'));
      else if($action=='accept_friend')
        $mngl_friends_controller->accept_friend($this->get_param('request_id'));
      else if($action=='ignore_friend')
        $mngl_friends_controller->ignore_friend($this->get_param('request_id'));
      else if($action=='search')
        $mngl_friends_controller->list_friends($this->get_param('mdp'),$this->get_param('mu'),true,$this->get_param('sq'));
    }
    else if($controller=='boards')
    {
      if($action=='post')
        $mngl_boards_controller->post($this->get_param('owner_id'), $this->get_param('author_id'), $this->get_param('message'));
      else if($action=='comment')
        $mngl_boards_controller->comment($this->get_param('board_post_id'), $this->get_param('author_id'), $this->get_param('message'));
      else if($action=='delete_post')
        $mngl_boards_controller->delete_post($this->get_param('board_post_id'));
      else if($action=='delete_comment')
        $mngl_boards_controller->delete_comment($this->get_param('board_comment_id'));
      else if($action=='older_posts')
        $mngl_boards_controller->show_older_posts($this->get_param('mu'),$this->get_param('mdp'),$this->get_param('loc'));
      else if($action=='clear_status')
        $mngl_boards_controller->clear_status($this->get_param('mu'));
    }
    else if($controller=='activity')
    {
      if($action=='post')
        $mngl_boards_controller->post($this->get_param('owner_id'), $this->get_param('author_id'), $this->get_param('message'),true);
      else if($action=='comment')
        $mngl_boards_controller->comment($this->get_param('board_post_id'), $this->get_param('author_id'), $this->get_param('message'),true);
      else if($action=='delete_post')
        $mngl_boards_controller->delete_post($this->get_param('board_post_id'),true);
      else if($action=='delete_comment')
        $mngl_boards_controller->delete_comment($this->get_param('board_comment_id'),true);
    }
    else if($controller=='profile')
    {  
      if($action=='delete_avatar')
        $mngl_profiles_controller->delete_avatar($this->get_param('user_id'));
      else if($action=='search')
        $mngl_profiles_controller->directory($this->get_param('mdp'),true,$this->get_param('sq'));
    }
    else if($controller=='options')
    {
      if($action=='add_default_user')
        $mngl_options_controller->display_default_friend_drop_down();
      else if($action=='add_custom_field')
        $mngl_options_controller->display_custom_field($this->get_param('index'));
      else if($action=='add_custom_field_option')
        $mngl_options_controller->display_custom_field_option( $this->get_param('field_index'), 
                                                               $this->get_param('option_index') );
    }
    else if($controller=='js')
    {
      if($action=='mingle')
        $this->mingle_js();
    }
    else if($controller=='captcha')
    {
      if($action=='display')
        $mngl_captcha_controller->display_captcha($this->get_param('width','120'), $this->get_param('height', '40'), $this->get_param('code', ''));
    }
    else if($controller=='messages')
    {
      if($action=='lookup_friends')
        $mngl_messages_controller->lookup_friends($this->get_param('sq'));
      else if($action=='mngl_process_reply_form')
        $mngl_messages_controller->create_reply( $this->get_param('mngl_thread_id'),
                                                 $this->get_param('mngl_reply') );
      else if($action=='delete_thread')
        $mngl_messages_controller->delete_thread( $this->get_param('t') );
      else if($action=='delete_threads')
        $mngl_messages_controller->delete_threads( $this->get_param('ts') );
      else if($action=='mark_read')
        $mngl_messages_controller->mark_unread_statuses( $this->get_param('ts'), 0 );
      else if($action=='mark_unread')
        $mngl_messages_controller->mark_unread_statuses( $this->get_param('ts'), 1 );
    }
  }
  
  function load_language()
  {
    $path_from_plugins_folder = str_replace( ABSPATH, '', MNGL_PATH ) . '/i18n/';
    
    load_plugin_textdomain( 'mingle', $path_from_plugins_folder );
  }
  
  function mingle_init()
  {
  	add_filter('get_avatar', array($this,'override_avatar'), 10, 4);
    add_filter('get_comment_author_url', array($this,'override_author_url'));
  }
  
  function override_author_url($url)
  {
    global $comment;
    
    $user = MnglUser::get_stored_profile_by_id($comment->user_id, false);
    
    if($user)
      return $user->get_profile_url();
    else
      return $url;
  }
  
  function override_avatar($avatar, $id_or_email, $size, $default)
  {
    $user_id = false;

    if( is_object($id_or_email) and $id_or_email->comment_author_email )
      $user_id = (int)MnglUtils::get_user_id_by_email($id_or_email->comment_author_email);
    else if( is_numeric($id_or_email) )
      $user_id = (int)$id_or_email;
    else if( is_string($id_or_email) )
      $user_id = (int)MnglUtils::get_user_id_by_email($id_or_email);
    
    if(!$user_id or empty($user_id))
      return $avatar;

    $avatar = MnglAppHelper::get_avatar_img_by_id($user_id, $size, $avatar);
    return $avatar;
  }

  // Utility function to grab the parameter whether it's a get or post
  function get_param($param, $default='')
  {
    return (isset($_REQUEST[$param])?$_REQUEST[$param]:$default);
  }
  
  function get_param_delimiter_char($link)
  { 
    return ((preg_match("#\?#",$link))?'&':'?');
  }
  
  function set_wp_mail_return_path($args)
  {
    // Apparently wp_mail ignores the Return-Path
    // header so let's set it manually here...
    $args->Sender = get_option('admin_email');
  }

  function set_wp_mailer($args)
  {
    global $mngl_options;
    
    if( isset($mngl_options->mailer['type']) and 
        $mngl_options->mailer['type'] == 'sendmail' )
    {
      $args->IsSendmail();
      $args->Sendmail = $mngl_options->mailer['sendmail-path'];
    }
    else if( isset($mngl_options->mailer['type']) and 
             $mngl_options->mailer['type'] == 'smtp' )
    {
      $args->IsSMTP();
      $args->Host       = $mngl_options->mailer['smtp-host'];
      $args->Port       = $mngl_options->mailer['smtp-port'];
      $args->SMTPSecure = $mngl_options->mailer['smtp-secure'];
      
      if( !empty($mngl_options->mailer['smtp-username']) and
          !empty($mngl_options->mailer['smtp-password']) )
      {
        $args->SMTPAuth = true;
        $args->Username = $mngl_options->mailer['smtp-username'];
        $args->Password = $mngl_options->mailer['smtp-password'];
      }
    }
  }
}
?>
