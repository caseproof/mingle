<?php
class MnglOptions
{
  // Page Setup Variables
  var $profile_page_id;
  var $profile_edit_page_id;
  var $friends_page_id;
  var $friend_requests_page_id;
  var $activity_page_id;
  var $directory_page_id;
  var $login_page_id;
  var $signup_page_id;
  var $inbox_page_id;
  var $inbox_page_id_str;
  
  var $profile_page_id_str;
  var $profile_edit_page_id_str;
  var $friends_page_id_str;
  var $friend_requests_page_id_str;
  var $activity_page_id_str;
  var $directory_page_id_str;
  var $login_page_id_str;
  var $signup_page_id_str;
  
  // Is the setup sufficiently completed for mingle to function?
  var $setup_complete;
  
  // Activity Types
  var $activity_types;
  
  // Notification Types
  var $notification_types;
  
  // Default Friends -- these guys are added automatically when users sign up
  var $default_friends;
  var $default_friends_str;
  
  // Invisible Users -- these guys aren't visible by Mingle
  var $invisible_users;
  var $invisible_users_str;
  
  // Pretty Profile Urls
  var $pretty_profile_urls;
  var $pretty_profile_urls_str;
  
  // Option to enable dns lookups on emails in the registration process
  var $signup_spam_email_protection;
  var $signup_spam_email_protection_str;
  
  // Option to enable robot detection in the registration process
  var $signup_robot_protection;
  var $signup_robot_protection_str;
  
  // Option to enable captcha in Signup Process
  var $signup_captcha;
  var $signup_captcha_str;

  // What fields to show where
  var $field_visibilities;
  var $field_visibility_str;
  
  var $prevent_admin_access;
  var $prevent_admin_access_str;
  
  var $mailer;
  var $mailer_str;
  
  var $custom_fields;
  var $custom_fields_str;
  
  var $show_powered_by;
  var $show_powered_by_str;

  function MnglOptions()
  {
    $this->set_default_options();
  }

  function set_default_options()
  {
    if(!isset($this->profile_page_id))
      $this->profile_page_id = 0;

    if(!isset($this->profile_edit_page_id))
      $this->profile_edit_page_id = 0;

    if(!isset($this->friends_page_id))
      $this->friends_page_id = 0;

    if(!isset($this->friend_requests_page_id))
      $this->friend_requests_page_id = 0;

    if(!isset($this->activity_page_id))
      $this->activity_page_id = 0;

    if(!isset($this->directory_page_id))
      $this->directory_page_id = 0;
      
    if(!isset($this->login_page_id))
      $this->login_page_id = 0;
      
    if(!isset($this->signup_page_id))
      $this->signup_page_id = 0;

    if(!isset($this->inbox_page_id))
      $this->inbox_page_id = 0;

    $this->profile_page_id_str         = 'mngl-profile-page-id';
    $this->profile_edit_page_id_str    = 'mngl-profile-edit-page-id';
    $this->friends_page_id_str         = 'mngl-friends-page-id';
    $this->friend_requests_page_id_str = 'mngl-friend-requests-page-id';
    $this->activity_page_id_str        = 'mngl-activity-page-id';
    $this->directory_page_id_str       = 'mngl-directory-page-id';
    $this->login_page_id_str           = 'mngl-login-page-id';
    $this->signup_page_id_str          = 'mngl-signup-page-id';
    $this->inbox_page_id_str           = 'mngl-inbox-page-id';
    
    if( $this->profile_page_id == 0 or
        $this->profile_edit_page_id == 0 or
        $this->friends_page_id == 0 or
        $this->friend_requests_page_id == 0 or
        $this->activity_page_id == 0 )
      $this->setup_complete = 0;
    else
      $this->setup_complete = 1;

    $this->field_visibility_str = "mngl-field-visibilies";
    if(!isset($this->field_visibilities))
    {
      $default_array = array( 'custom' => array(),
                              'name' => 'on',
                              'url' => 'on',
                              'location' => 'on',
                              'sex' => 'on',
                              'bio' => 'on',
                              'birthday' => 'on' );

      $this->field_visibilities['profile_edit']  = array_merge($default_array, array('password' => 'on'));
      $this->field_visibilities['profile_front'] = $default_array;
      $this->field_visibilities['profile_info']  = $default_array;
      $this->field_visibilities['signup_page']   = array( 'name' => 'on', 'sex' => 'on', 'password' => 'on' );

      if(isset($this->show_name) and !$this->show_name)
      {
        unset($this->field_visibilities['profile_edit']['name']);
        unset($this->field_visibilities['profile_front']['name']);
        unset($this->field_visibilities['profile_info']['name']);
        unset($this->show_name);
      }

      if(isset($this->show_url) and !$this->show_url)
      {
        unset($this->field_visibilities['profile_edit']['url']);
        unset($this->field_visibilities['profile_front']['url']);
        unset($this->field_visibilities['profile_info']['url']);
        unset($this->show_url);
      }

      if(isset($this->show_location) and !$this->show_location)
      {
        unset($this->field_visibilities['profile_edit']['location']);
        unset($this->field_visibilities['profile_front']['location']);
        unset($this->field_visibilities['profile_info']['location']);
        unset($this->show_location);
      }

      if(isset($this->show_sex) and !$this->show_sex)
      {
        unset($this->field_visibilities['profile_edit']['sex']);
        unset($this->field_visibilities['profile_front']['sex']);
        unset($this->field_visibilities['profile_info']['sex']);
        unset($this->show_sex);
      }

      if(isset($this->show_bio) and !$this->show_bio)
      {
        unset($this->field_visibilities['profile_edit']['bio']);
        unset($this->field_visibilities['profile_front']['bio']);
        unset($this->field_visibilities['profile_info']['bio']);
        unset($this->show_bio);
      }

      if(isset($this->show_birthday) and !$this->show_birthday)
      {
        unset($this->field_visibilities['profile_edit']['birthday']);
        unset($this->field_visibilities['profile_front']['birthday']);
        unset($this->field_visibilities['profile_info']['birthday']);
        unset($this->show_birthday);
      }

      if(isset($this->signup_page_fields))
      {
        $this->field_visibilities['signup_page'] = $this->signup_page_fields;
        unset($this->signup_page_fields);
      }
    }

    if(!isset($this->default_friends))
    {
      if(isset($this->default_friend))
      {
        // Default Friend Migration
        $this->default_friends[] = $this->default_friend;
        unset($this->default_friend);
      }
      else
        $this->default_friends = array();
    }

    $this->default_friends_str = 'mngl-default-friends';

    if(!isset($this->invisible_users))
      $this->invisible_users = array();
    $this->invisible_users_str = 'mngl-invisible-users';

    if(!isset($this->pretty_profile_urls))
      $this->pretty_profile_urls = false;
    $this->pretty_profile_urls_str = 'mngl-pretty-profile-urls';

    if(!isset($this->signup_spam_email_protection))
      $this->signup_spam_email_protection = false;
    $this->signup_spam_email_protection_str = 'mngl_spam_email_protection';

    if(!isset($this->signup_robot_protection))
      $this->signup_robot_protection = false;
    $this->signup_robot_protection_str = 'mngl_robot_protection';

    if(!isset($this->signup_captcha))
      $this->signup_captcha = false;
    $this->signup_captcha_str = 'mngl_captcha';

    if(!isset($this->prevent_admin_access))
      $this->prevent_admin_access = false;
    $this->prevent_admin_access_str = 'mngl_prevent_admin_access';
	
    if(!isset($this->show_powered_by))
      $this->show_powered_by = true;
    $this->show_powered_by_str = 'show_powered_by';
    
    if(!isset($this->mailer))
      $this->mailer = array( 'type'          => 'mail',
                             'sendmail-path' => '/usr/sbin/sendmail',
                             'smtp-host'     => 'localhost',
                             'smtp-port'     => '25',
                             'smtp-secure'   => '',
                             'smtp-username' => '',
                             'smtp-password' => '' );
    $this->mailer_str = 'mngl_mailer';
    
    if(!isset($this->custom_fields))
      $this->custom_fields = array();
    $this->custom_fields_str = 'mngl_custom_fields';
  }
  
  function validate($params,$errors)
  {
    $errors = apply_filters( 'mngl_validate_options', $errors );

    return $errors;
  }
  
  function update($params)
  {
    $this->update_page('profile_page', $params);
    $this->update_page('profile_edit_page', $params);
    $this->update_page('friends_page', $params);
    $this->update_page('friend_requests_page', $params);
    $this->update_page('activity_page', $params);
    $this->update_page('directory_page', $params);
    $this->update_page('login_page', $params);
    $this->update_page('signup_page', $params);
    $this->update_page('inbox_page', $params);
    
    if( !is_numeric($params[$this->signup_page_id_str]) and
        preg_match("#^__auto_page:(.*?)$#",$params[$this->signup_page_id_str],$matches) )
      $this->signup_page_id = $params[$this->signup_page_id_str] = $this->auto_add_page($matches[1]);
    else
      $this->signup_page_id = (int)$params[$this->signup_page_id_str];

    if( !is_numeric($params[$this->login_page_id_str]) and
        preg_match("#^__auto_page:(.*?)$#",$params[$this->login_page_id_str],$matches) )
      $this->login_page_id = $params[$this->login_page_id_str] = $this->auto_add_page($matches[1]);
    else
      $this->login_page_id = (int)$params[$this->login_page_id_str];
    $this->field_visibilities      = $params[ $this->field_visibility_str ];
    
    $this->default_friends         = $params[ $this->default_friends_str ];
    $this->invisible_users         = $params[ $this->invisible_users_str ];
    $this->pretty_profile_urls     = isset($params[ $this->pretty_profile_urls_str ]);
    $this->signup_spam_email_protection = isset($params[ $this->signup_spam_email_protection_str ]);
    $this->signup_robot_protection = isset($params[ $this->signup_robot_protection_str ]);
    $this->signup_captcha          = isset($params[ $this->signup_captcha_str ]);
    $this->prevent_admin_access    = isset($params[ $this->prevent_admin_access_str ]);
    $this->show_powered_by    = isset($params[ $this->show_powered_by_str ]);
    $this->mailer        = $params[ $this->mailer_str ];
    $this->smtp_settings = $params[ $this->smtp_settings_str ];
    $this->custom_fields = $params[ $this->custom_fields_str ];
    
    do_action( 'mngl_update_options', $params );
  }
  
  function store()
  {
    global $mngl_custom_field;

    if(is_array($this->custom_fields) and !empty($this->custom_fields))
      $mngl_custom_field->update_fields_from_array($this->custom_fields);

    unset($this->custom_fields); // we just use the array for setting the db

    // Save the posted value in the database
    update_option( 'mngl_options', $this );
    
    do_action( 'mngl_store_options' );
  }
  
  /** Allows custom plugins to register activity types. Each type should contain the following fields:
    * $activity_types['cool_activities'] = array( 'name' => 'Cool Activities',
    *                                               'description' => 'These are some really cool activities punk',
    *                                               'message' => '{$owner->screenname} did some cool stuff',
    *                                               'icon' => '/wp-content/plugin/cool-activities/images/cool_plugin.png');
    */
  function set_activity_types()
  {
    $this->activity_types = array();
    $this->activity_types = apply_filters('mngl-activity-types', $this->activity_types);
  }
  
  /** Allows custom plugins to register notification types. Each type should contain the following fields:
    * $notification_types['cool_activities'] = array( 'name' => 'Cool Notifications',
    *                                                   'description' => 'I\'m going to email this guy like none other');
    */
  function set_notification_types()
  {
    $this->notification_types = array();
    $this->notification_types = apply_filters('mngl-notification-types', $this->notification_types);
  }
  
  /** Allows custom plugins to register notification types. Each type should contain the following fields:
    * $notification_types['cool_activities'] = array( 'name' => 'Cool Notifications',
    *                                                   'description' => 'I\'m going to email this guy like none other');
    */
  function set_default_friends()
  {
    $this->default_friends = apply_filters('mngl-default-friends', $this->default_friends);
  }

  function auto_add_page($page_name)
  {
    return wp_insert_post(array('post_title' => $page_name, 'post_type' => 'page', 'post_status' => 'publish', 'comment_status' => 'closed'));
  }
  
  function update_page($page_name, &$params)
  {
    $page_name_id = $page_name . "_id";
    $page_name_str = $page_name_id . "_str";
    if( !is_numeric($params[$this->$page_name_str]) and
        preg_match("#^__auto_page:(.*?)$#",$params[$this->$page_name_str],$matches) )
      $this->$page_name_id = $params[$this->$page_name_str] = $this->auto_add_page($matches[1]);
    else
      $this->$page_name_id = (int)$params[$this->$page_name_str];
  }
}
?>
