<?php

class MnglUser
{ 
  var $profile_name;
  
  var $userdata;

  var $id; // ID from the current user record
  var $avatar;
  var $avatars;
  var $first_name;
  var $last_name;
  var $full_name; // Display Name
  var $screenname;
  var $email;
  var $url;
  var $location;
  var $sex;
  var $sex_display;
  var $bio;
  var $birthday;
  var $fields;
  var $hashed_password;
  
  var $id_str;
  var $avatar_str;
  var $first_name_str;
  var $last_name_str;
  var $screenname_str;
  var $email_str;
  var $url_str;
  var $location_str;
  var $sex_str;
  var $bio_str;
  var $birthday_str;
  var $fields_str;
  var $password_str;
  var $password_confirm_str;
  var $user_status_str;
  var $user_status_time_str;
  var $user_status_time_ts_str;
  
  var $his_her; // calculated from $sex var
  var $him_her; // calculated from $sex var
  var $he_she; // calculated from $sex var

  // Notification Settings
  var $hide_notifications;
  
  var $hide_notifications_str;
  
  // Privacy Settings
  var $privacy;
  
  var $privacy_str;
  
  var $defaults_have_been_set;

  function MnglUser( $id = '')
  {
    $this->set_default_options( $id );
  }

  function set_default_options( $id = '' )
  {
    global $mngl_options, $current_user, $wpdb;
    MnglUtils::get_currentuserinfo();
    
    if( empty($id) )
      $this->userdata =& $current_user;
    else
      $this->userdata =& MnglUtils::get_userdata($id);
    
    // We're abstracting and simplifying user objects here
    $this->id         = $this->_get_metadata( 'ID' );
    $this->screenname = $this->_get_metadata( 'user_login' );
    $this->email      = $this->_get_metadata( 'user_email' );
    $this->url        = $this->_get_metadata( 'user_url' );

    // Set string keys
    $this->profile_name            = 'mngl_user_profile';
    $this->id_str                  = 'mngl_user_id';
    $this->avatar_str              = 'mngl_user_avatar';
    $this->first_name_str          = 'mngl_user_first-name';
    $this->last_name_str           = 'mngl_user_last-name';
    $this->screenname_str          = 'mngl_user_screenname';
    $this->email_str               = 'mngl_user_email';
    $this->url_str                 = 'mngl_user_url';
    $this->location_str            = 'mngl_user_location';
    $this->sex_str                 = 'mngl_user_sex';
    $this->bio_str                 = 'mngl_user_bio';
    $this->birthday_str            = 'mngl_user_birthday';
    $this->fields_str              = 'mngl_user_fields';
    $this->hide_notifications_str  = 'mngl_user_hide_notifications';
    $this->privacy_str             = 'mngl_user_privacy';
    $this->password_str            = 'mngl_password';
    $this->password_confirm_str    = 'mngl_password_confirm';
    $this->user_status_str         = 'mngl-user-status';
    $this->user_status_time_str    = 'mngl-user-status-time';
    $this->user_status_time_ts_str = 'mngl-user-status-time-ts';
    
    // Get some metadata aggregated dogg!
    $this->first_name         = $this->_get_metadata( 'first_name' );
    $this->last_name          = $this->_get_metadata( 'last_name' );
    $this->bio                = $this->_get_metadata( 'description' );
    $this->avatar             = $this->_get_metadata( 'mngl_avatar' );
    $this->avatars            = $this->_get_metadata( 'mngl_avatars' );
    $this->location           = $this->_get_metadata( 'mngl_location' );
    $this->sex                = $this->_get_metadata( 'mngl_sex' );
    $this->fields             = $this->_get_metadata( 'mngl_fields' );
    $this->hide_notifications = $this->_get_metadata( $this->hide_notifications_str );
    $this->privacy            = $this->_get_metadata( $this->privacy_str );

    // translators: Please don't translate this string -- it is the php formatting of dates. Please modify it accordingly for your locale by following the instructions on this page: http://us3.php.net/manual/en/function.date.php
    $date_format = __('F j, Y', 'mingle');
    
    $birthday_timestamp = (int)$this->_get_metadata( 'mngl_birthday' );
    if(isset($birthday_timestamp) and !empty($birthday_timestamp) and $birthday_timestamp)
      $this->birthday = date( $date_format, $birthday_timestamp );
    else
      $this->birthday = '';

    if(!empty($this->first_name)){
      $this->full_name = $this->first_name;
      if (!empty($this->last_name))
        $this->full_name .= " {$this->last_name}";
    }else
      $this->full_name = $this->screenname;

    // Set Defaults
    if(!isset($this->hide_notifications) or !is_array($this->hide_notifications))
      $this->hide_notifications = array();

    if(!isset($this->privacy) or !$this->privacy)
      $this->privacy = 'private';

    // Default -- large size avatar
    if(!$this->_guess_avatar())
      $this->avatar = '';
    else
    {
      // If the avatar is too big the let's downsize it...
      $avmeta = $this->get_avatar_meta();

      if( (isset($avmeta[0]) && (int)$avmeta[0] > 600) or
          (isset($avmeta[1]) && (int)$avmeta[1] > 600) )
      {
        require_once(ABSPATH.'wp-admin/includes/image.php');
        require_once(ABSPATH.'wp-includes/media.php');
        $resized_image = image_resize( $this->_avatar_path() . "/" . $this->avatar, 600, 600, false, null, $this->_avatar_path() );

        if ( !is_wp_error($resized_image) and $resized_image )
        {
          $this->avatar = basename( $resized_image );
          $this->_update_metadata( 'mngl_avatar', $this->avatar );
        }
      }
    }
    
    if(!isset($this->avatars) or !$this->avatars or !is_array($this->avatars))
      $this->avatars = array();

    if(!isset($this->sex) or !$this->sex)
      $this->sex = '';

    if(!isset($this->hashed_password) or !$this->hashed_password)
      $this->hashed_password = '';

    if(!isset($this->fields) or !$this->fields)
      $this->fields = array();
    
    // Calculate pronouns to use depending on gender
    if($this->sex == 'female')
    {
      $this->his_her = __('Her', 'mingle');
      $this->him_her = __('Her', 'mingle');
      $this->he_she  = __('She', 'mingle');
      $this->sex_display = __('Female', 'mingle');
    }
    else if($this->sex == 'male')
    {
      $this->his_her = __('His', 'mingle');
      $this->him_her = __('Him', 'mingle');
      $this->he_she  = __('He', 'mingle');
      $this->sex_display = __('Male', 'mingle');
    }
    else
    {
      $this->his_her = __('His/Her', 'mingle');
      $this->him_her = __('Him/Her', 'mingle');
      $this->he_she  = __('He/She', 'mingle');
      $this->sex_display = __('Not Available', 'mingle');
    }
    
    $this->defaults_have_been_set = true;
  }
  
  // Try to guess avatar location from a sized avatar
  // Purely for robustness ... :)
  function _guess_avatar()
  {
    if( !isset($this->avatar) or !$this->avatar or empty($this->avatar) or !file_exists($this->_avatar_path() . "/" . $this->avatar) )
    {
      if( isset($this->avatars) and 
          !empty($this->avatars) and
          is_array($this->avatars))
      {
        foreach($this->avatars as $type)
        {
          foreach($type as $sized)
          {
            // Just strip the resizing portion of the file see if it exists
            $avatar_guess = preg_replace('#-\d+x\d+\.#', '.', $sized);
            if( file_exists($this->_avatar_path() . "/" . $avatar_guess) and 
                MnglUtils::is_image($this->_avatar_path() . "/" . $avatar_guess) )
            {
              $this->avatar = $avatar_guess;
              $this->_update_metadata( 'mngl_avatar', $this->avatar );
              break;
            }
          }
        
          if(!empty($this->avatar))
            break;
        }  
      }
    }
    
    // If we have something in $this->avatar then we were successful
    return !empty($this->avatar);
  }
  
  // This is basically for caching purposes
  function _update_metadata($meta_key,$meta_value)
  {
    global $mngl_utils;
    
    // Strip dashes just like get_usermeta
    $meta_key = str_replace("-","",$meta_key);

    $status = update_usermeta($this->id,$meta_key,$meta_value);
    $this->userdata->{$meta_key} = $meta_value;

    return $status;
  }

  // This is basically for caching purposes
  function _get_metadata($meta_key)
  {
    // Strip dashes just like get_usermeta
    $meta_key = str_replace("-","",$meta_key);

    if(isset($this->userdata->{$meta_key}))
      return $this->userdata->{$meta_key};
    else
      return false;
  }
  
  // for convenience
  function _delete_metadata($meta_key)
  {
    global $mngl_utils;

    // Strip dashes just like get_usermeta
    $meta_key = str_replace("-","",$meta_key);

    $status = delete_usermeta($this->id,$meta_key);
    unset($this->userdata->{$meta_key});
    
    return $status;
  }
  
  function validate_signup($params,$errors)
  {
    global $mngl_options;

    extract($params);
    
    if($mngl_options->signup_robot_protection and MnglUtils::is_robot())
      $errors[] = __('This site prevents robots from registering and you don\'t appear to be human. If you\'re a real person, please contact the administrator of this site.', 'mingle');

    if(empty($user_login))
      $errors[] = __('Username must not be blank','mingle');

    if(!preg_match('#^[a-zA-Z0-9_]+$#',$user_login))
      $errors[] = __('Username must only contain letters, numbers and/or underscores','mingle');
    
    require_once(ABSPATH . WPINC . '/registration.php');
    $user_id = username_exists( $user_login );
    $available = MnglUtils::username_is_available( $user_login );
    if ( $user_id or !$available)
    	$errors[] = __('Username is Already Taken.','mingle');
    
    if(empty($user_email))
      $errors[] = __('Email must not be blank','mingle');
      
    if(!is_email($user_email, $mngl_options->signup_spam_email_protection))
      $errors[] = __('Email must be a real and properly formatted email address','mingle');
      
    if(email_exists($user_email))
      $errors[] = __('Email Address has already been used by another user.','mingle');

    if(isset($mngl_options->field_visibilities['signup_page']['sex']) and empty($mngl_user_sex))
      $errors[] = __('You must select your Gender.','mingle');

    // Captcha
    if($mngl_options->signup_captcha)
    {
      $captcha_code = MnglUtils::str_decrypt($security_check);
      if(!empty($captcha_code) and $captcha_code != $security_code)
        $errors[] = __('Captcha Image Verification Was Incorrect, Please Try Again','mingle');
    }
      
    if(isset($mngl_options->field_visibilities['signup_page']['password']))
    {
      if(empty($mngl_user_password))
        $errors[] = __('You must enter a Password.','mingle');
        
      if(empty($mngl_user_password_confirm))
        $errors[] = __('You must enter a Password Confirmation.', 'mingle');
        
      if($mngl_user_password != $mngl_user_password_confirm)
        $errors[] = __('Your Password and Password Confirmation don\'t match.', 'mingle');
    }
    
    return $errors;
  }
  
  function validate_login($params,$errors)
  {
    extract($params);

    if(empty($log))
      $errors[] = __('Username must not be blank','mingle');

    if(!function_exists('username_exists'))
      require_once(ABSPATH . WPINC . '/registration.php');

    if(!username_exists($log))
      $errors[] = __('Username was not found','mingle');
    else
    {
      if(!function_exists('user_pass_ok'))
        require_once(ABSPATH . WPINC . '/user.php');

      if(!user_pass_ok($log, $pwd))
        $errors[] = __('Your Password was Incorrect','mingle');
    }

    return $errors;
  }
  
  function validate_forgot_password($params,$errors)
  {
    extract($params);

    if(empty($mngl_user_or_email))
      $errors[] = __('You must enter a Username or Password','mingle');
    else
    {
      if(!function_exists('username_exists') or !function_exists('email_exists'))
        require_once(ABSPATH . WPINC . '/registration.php');

      $is_email = (is_email($mngl_user_or_email) and email_exists($mngl_user_or_email));
      $is_username = username_exists($mngl_user_or_email);
      
      if(!$is_email and !$is_username)
        $errors[] = __('That Username or Email wasn\'t found.','mingle');
    }

    return $errors;    
  }

  function validate_reset_password($params,$errors)
  {
    extract($params);

    if(empty($mngl_user_password))
      $errors[] = __('You must enter a Password.','mingle');
      
    if(empty($mngl_user_password_confirm))
      $errors[] = __('You must enter a Password Confirmation.', 'mingle');
      
    if($mngl_user_password != $mngl_user_password_confirm)
      $errors[] = __('Your Password and Password Confirmation don\'t match.', 'mingle');
    
    return $errors;
  }
  
  function validate($params,$errors)
  {   
    if( isset( $params[ $this->password_str ] ) and
        !empty( $params[ $this->password_str ]) and
        ( $params[ $this->password_str ] != $params[ $this->password_confirm_str ] ) )
    {
      $errors[] = __("Password Must Match Password Confirmation.", 'mingle');
    }

    // Validate the avatar file
    if( isset($_FILES[ $this->avatar_str ]) and 
        !empty($_FILES[ $this->avatar_str ]['name']) and
        (int)$_FILES[ $this->avatar_str ]['size'] > 0 and
        !file_exists($_FILES[ $this->avatar_str ]['tmp_name']))
    {
      $errors[] = __("Your Avatar wasn't able to be saved.", 'mingle');
    }
    
    // Validate the avatar file type
    if( isset($_FILES[ $this->avatar_str ]) and 
        !empty($_FILES[ $this->avatar_str ]['name']) and
        (int)$_FILES[ $this->avatar_str ]['size'] > 0 and
        !MnglUtils::is_image($_FILES[ $this->avatar_str ]['tmp_name']))
    {
      $errors[] = __("Your Avatar must be a valid jpg, gif or png.", 'mingle');
    }
    
    return $errors;
  }
  
  function update($params)
  {
    global $mngl_options;
    
    // Only the current user can modify his/her own profile
    if($this->is_logged_in_and_current_user($this->id))
    { 
      $this->id                 = (int)$params[ $this->id_str ];
      $this->first_name         = $params[ $this->first_name_str ];
      $this->last_name          = $params[ $this->last_name_str ];
      $this->bio                = $params[ $this->bio_str ];
      $this->screenname         = $params[ $this->screenname_str ];
      $this->email              = $params[ $this->email_str ];
      $this->url                = $params[ $this->url_str ];
      $this->location           = $params[ $this->location_str ];
      $this->sex                = $params[ $this->sex_str ];
      $this->fields             = $params[ $this->fields_str ];
      $this->hide_notifications = $params[ $this->hide_notifications_str ];
      $this->privacy            = $params[ $this->privacy_str ];
      
      if( isset($params[ $this->birthday_str ]) and
          !empty($params[ $this->birthday_str ]) and
          $params[ $this->birthday_str ] )
        $this->birthday = strtotime( $params[ $this->birthday_str ] );
      else
        $this->birthday = '';
      
      if( isset( $params[ $this->password_str ] ) and
          !empty( $params[ $this->password_str ]) and
          ( $params[ $this->password_str ] == $params[ $this->password_confirm_str ] ) )
      {
        $this->hashed_password = MnglUtils::wp_hash_password( $params[ $this->password_str ] );
      }
      
      // Upload the avatar 
      if( isset($_FILES[ $this->avatar_str ]) and 
          !empty($_FILES[ $this->avatar_str ]['name']) and
          (int)$_FILES[ $this->avatar_str ]['size'] > 0 )
      {
        $target_path_array = wp_upload_dir();
        $target_path = $target_path_array['basedir'];
        
        if(!file_exists($target_path))
          @mkdir($target_path."/");
      
        $target_path = $target_path . "/mingle";
        if(!file_exists($target_path))
          @mkdir($target_path."/");
    
        $target_path = $target_path . "/avatars";
        if(!file_exists($target_path))
          @mkdir($target_path."/");
        
        // Using WordPress' built in resize capabilies using GD
        require_once(ABSPATH.'wp-admin/includes/image.php');
        require_once(ABSPATH.'wp-includes/media.php');
        $tmp_image = $target_path . '/tmp_' . md5(trim(strtolower($this->email)));
        move_uploaded_file($_FILES[ $this->avatar_str ]['tmp_name'], $tmp_image);
        $resized_image = image_resize( $tmp_image, 600, 600, false, null, $target_path );
      
        if ( !is_wp_error($resized_image) and $resized_image )
        {
          $full_image = $resized_image;
          unlink($tmp_image);
        }
        else
          $full_image = $tmp_image;
        
        $avatar_meta = getimagesize($full_image);
        $ext = MnglAppHelper::get_extension($avatar_meta['mime']); 
        $image_path = $target_path . '/' . md5(trim(strtolower($this->email))) . "_" . time() . ".{$ext}";

        // Rename the full image
        copy($full_image, $image_path);
        unlink($full_image);

        $this->delete_avatars();
        $this->avatar = basename($image_path);
      }
    }
  }
  
  function _avatar_path()
  {
    $avatar_path_array = wp_upload_dir();
    $avatar_path = $avatar_path_array['basedir'];
    return "{$avatar_path}/mingle/avatars";
  }

  function _avatar_url()
  {
    $avatar_url_array = wp_upload_dir();
    $avatar_url = $avatar_url_array['baseurl'];
    return "{$avatar_url}/mingle/avatars";
  }

  function delete_avatars()
  {
    if(isset($this->avatar) and !empty($this->avatar))
    {
      if(file_exists($this->_avatar_path() . "/" . $this->avatar))
        unlink($this->_avatar_path() . "/" . $this->avatar);

      unset($this->avatar);
      delete_usermeta( $this->id, 'mngl_avatar' );
    }
    
    $this->delete_sized_avatars();
  }
  
  function delete_sized_avatars()
  {
    if(isset($this->avatars) and !empty($this->avatars) and is_array($this->avatars))
    {
      foreach($this->avatars as $type)
      {
        foreach($type as $sized)
        {
          if(file_exists($this->_avatar_path() . "/" . $sized))
            unlink($this->_avatar_path() . "/" . $sized);
        }
      }
        
      unset($this->avatars);
      delete_usermeta( $this->id, 'mngl_avatars' );
    }
  }
  
  function store($force = false)
  {
    global $wpdb;
    
    // Only the current user can store his/her own profile
    if($force or $this->is_logged_in_and_current_user($this->id))
    {
      wp_cache_delete("mngl_user", "{$this->id}"); // Clear the cache
  
      // Update User Table Fields
      if( !empty( $this->hashed_password ) )
      {
        $query = "UPDATE {$wpdb->users} SET user_login=%s, user_email=%s, user_url=%s, user_pass=%s WHERE id=%d";
        $query = $wpdb->prepare($query, $this->screenname, $this->email, $this->url, $this->hashed_password, $this->id);
      }
      else
      {
        $query = "UPDATE {$wpdb->users} SET user_login=%s, user_email=%s, user_url=%s WHERE id=%d";
        $query = $wpdb->prepare($query, $this->screenname, $this->email, $this->url, $this->id);
      }
      $wpdb->query($query);
      
      // Update User Meta Fields
      $this->_update_metadata( 'first_name',             $this->first_name );
      $this->_update_metadata( 'last_name',              $this->last_name );
      $this->_update_metadata( 'mngl_avatar',            $this->avatar );
      $this->_update_metadata( 'mngl_location',          $this->location );
      $this->_update_metadata( 'mngl_sex',               $this->sex );
      $this->_update_metadata( 'description',            $this->bio );
      $this->_update_metadata( 'mngl_birthday',          $this->birthday );
      $this->_update_metadata( 'mngl_fields',            $this->fields );

      $this->_update_metadata( $this->hide_notifications_str, $this->hide_notifications );
      $this->_update_metadata( $this->privacy_str,            $this->privacy );

      $this->set_default_options( $this->id );
    }
  }
  
  function get_all( $where='', $order_by='', $limit='' )
  {
    global $wpdb, $mngl_friend, $user_id;
    
    $where    = ((empty($where))?'':" WHERE {$where}");
    $order_by = ((empty($order_by))?'':" ORDER BY {$order_by}");
    $limit    = ((empty($limit))?'':" LIMIT {$limit}");
    
    $query_str = "SELECT * FROM {$wpdb->users}{$where}{$order_by}{$limit}";
    $query = $wpdb->prepare( $query_str, $user_id );

    return $wpdb->get_results($query);
  }
  
  function get_avatar($size='96', $linked=true)
  {
    $avatar = MnglUtils::get_avatar( $this->id, $size );
    $avatar = MnglAppHelper::add_avatar_class($avatar, 'mngl-profile-image');
    $avatar = MnglAppHelper::link_avatar( $this->id, $avatar );
    
    return $avatar;
  }
  
  function get_avatar_meta($avfile='')
  {
    if(empty($avfile))
      $avfile =& $this->avatar;

    $avmeta = false;
    if(!empty($avfile) and file_exists( $this->_avatar_path() . "/" . $avfile ))
      $avmeta = getimagesize( $this->_avatar_path() . "/" . $avfile );

    return array('width' => $avmeta[0], 'height' => $avmeta[1]);
  }
  
  function get_sized_avatar($size,$square=true)
  {
    // If an avatar isn't present then try to find it...
    if(!$this->_guess_avatar())
      return false;

    $type = (($square)?'square':'rect');

    if( !isset($this->avatars[$type][$size]) or
        empty($this->avatars[$type][$size]) or
        !file_exists($this->_avatar_path() . "/" . $this->avatars[$type][$size]) )
    {
      // resize if there isn't a thumb and there is an image
      // Using WordPress' built in resize capabilies using GD
      require_once(ABSPATH.'wp-admin/includes/image.php');
      require_once(ABSPATH.'wp-includes/media.php');
      $avmeta = $this->get_avatar_meta();

      $new_width = $size;
      if(!$square and $avmeta['width'] > 0 and $avmeta['height'] > 0)
        $new_height = (int)((float)$size / (float)$avmeta['width'] * (float)$avmeta['height']);
      else
        $new_height = $size;

      $resized_avatar = image_resize( ($this->_avatar_path() . "/" . $this->avatar), $new_width, $new_height, $square, null, $this->_avatar_path() );
      
      if( !is_wp_error($resized_avatar) and $resized_avatar )
      {
        if(!isset($this->avatars[$type]) or !is_array($this->avatars[$type]))
          $this->avatars[$type] = array();
        
        $this->avatars[$type][$size] = basename( $resized_avatar );
        
        $this->_update_metadata( 'mngl_avatars', $this->avatars );
      }
      else
      {
        $this->avatars[$type][$size] = $this->avatar;
      }
    }

    return $this->avatars[$type][$size];
    
  }
  
  function get_sized_avatar_url($size, $square=true)
  {
    global $mngl_blogurl;
    $avatar = $this->get_sized_avatar($size, $square);

    if($avatar)
      return $this->_avatar_url() . "/{$avatar}";
    else
      return false;
  }

  function get_sized_avatar_path($size, $square=true)
  {
    return $this->_avatar_path() . "/" . $this->get_sized_avatar($size, $square);
  }

  function get_avatar_dimensions($size, $square=true)
  {
    $avdim = false;
    if(is_numeric($size) and $size > 0)
    {
      if($square)
        $avdim = array("width" => $size, "height" => $size);
      else
        $avdim = $this->get_avatar_meta($this->get_sized_avatar_path($size, $square));
    }
    
    return $avdim;
  }
  
  function get_profile_url()
  {
    global $mngl_options;
    
    if(isset($mngl_options->profile_page_id) and $mngl_options->profile_page_id != 0)
    {
      if( MnglUtils::rewriting_on() and $mngl_options->pretty_profile_urls )
      {
        global $mngl_blogurl;
        $struct = MnglUtils::get_permalink_pre_slug_uri();
        return "{$mngl_blogurl}{$struct}{$this->screenname}";
      }
      else
      {
        $permalink = get_permalink($mngl_options->profile_page_id);
        $param_char = ((preg_match("#\?#",$permalink))?'&':'?');
        return "{$permalink}{$param_char}u={$this->screenname}";
      }
    }
    
    return '';
  }
  
  function get_profile_link($name='')
  {
    return '<a href="'.$this->get_profile_url().'">'.((empty($name))?$this->screenname:$name).'</a>';
  }
  
  function get_friends_url()
  {
    global $mngl_options;

    if(isset($mngl_options->friends_page_id) and $mngl_options->friends_page_id != 0)
    {
      $permalink = get_permalink($mngl_options->friends_page_id);
      $param_char = ((preg_match("#\?#",$permalink))?'&':'?');
      return "{$permalink}{$param_char}u={$this->screenname}";
    }
    
    return '';
  }
  
  function update_status($status)
  {
    if(!empty($status))
    {
      $this->_update_metadata( $this->user_status_str,  $status );
      $this->_update_metadata( $this->user_status_time_str, date('c') );
      $this->_update_metadata( $this->user_status_time_ts_str, time() );
    }
  }
  
  function get_status()
  {
    return $this->_get_metadata( $this->user_status_str );
  }

  function clear_status()
  {
    $this->_delete_metadata( $this->user_status_str );
    $this->_delete_metadata( $this->user_status_time_str );
    $this->_delete_metadata( $this->user_status_time_ts_str );
  }
  
  function get_status_time()
  {
    return $this->_get_metadata( $this->user_status_time_str );
  }
  
  function get_status_time_ts()
  {
    $status_time_ts = $this->_get_metadata( $this->user_status_time_ts_str );
    if(isset($status_time_ts) and $status_time_ts)
      return $status_time_ts;
    else
    {
      $status_time = $this->_get_metadata( $this->user_status_time_str );
      return strtotime($status_time);
    }
  }
  
  function &get_stored_profile_by_id($user_id = '', $load_default = true)
  { 
    return MnglUser::get_stored_profile($user_id, $load_default);
  }
  
  function &get_stored_profile_by_screenname($screenname = '', $load_default = true)
  {
    $user = '';
    
    if(!empty($screenname))
      $user = MnglUtils::get_userdatabylogin($screenname);
    
    return MnglUser::get_stored_profile($user->ID, $load_default);
  }
  
  function &get_stored_profile($user_id = '', $load_default = true)
  { 
    global $mngl_options;

    if((empty($user_id) and $load_default) or
       (!is_admin() and in_array($user_id, $mngl_options->invisible_users)))
    {
      global $current_user;
      MnglUtils::get_currentuserinfo();
      $user_id = $current_user->ID;
    }

    if($user_id and !empty($user_id) and is_numeric($user_id))
    {
      $user = wp_cache_get("mngl_user", "{$user_id}");
      if($user == false)
      {
      	$user = new MnglUser($user_id);
      	$expire = 24 * 60 * 60; // 1 whole day
      	wp_cache_set("mngl_user", $user, "{$user_id}", $expire);
    	}
    	
    	return $user;
    }
    
    return false;
  }
  
  function get_stored_profiles($search_query='', $offset=0, $limit=50, $order_by='user_login ASC')
  {
    global $wpdb, $mngl_options;
    $where = '';

    if((!is_admin() and !empty($mngl_options->invisible_users)) or !empty($search_query))
    {
      $where .= "WHERE ";

      // Hide invisibles
      if(!is_admin() and !empty($mngl_options->invisible_users))
        $where .= "ID NOT IN (" . implode(',',$mngl_options->invisible_users) . ")";
      
      if((!is_admin() and !empty($mngl_options->invisible_users)) and !empty($search_query))
        $where .= " AND ";
      
      if(!empty($search_query))
        $where .= "user_login LIKE '%{$search_query}%'";
    }

    $order_by  = ((!empty($order_by))?" ORDER BY {$order_by}":'');
    $limit_str = (($limit > 0)?" LIMIT {$offset},{$limit}":'');
    $query = "SELECT ID FROM {$wpdb->users} {$where}{$order_by}{$limit_str}";

    $user_ids = $wpdb->get_col($query,0);
    $profiles = array();
    
    if(empty($user_ids))
      return false;

    foreach ($user_ids as $user_id)
    {
      $curr_profile = MnglUser::get_stored_profile_by_id($user_id);

      if($curr_profile)
        $profiles[] = $curr_profile;
    }
    
    return $profiles;
  }
  
  function get_count($add_where='')
  {
    global $wpdb, $mngl_options;
    
    $where = '';

    if(!empty($mngl_options->invisible_users) or !empty($add_where))
    {
      $where .= " WHERE ";

      // Hide invisibles
      if(!is_admin() and !empty($mngl_options->invisible_users))
        $where .= "ID NOT IN (" . implode(',',$mngl_options->invisible_users) . ")";
      
      if((!is_admin() and !empty($mngl_options->invisible_users)) and !empty($add_where))
        $where .= " AND ";
      
      if(!empty($add_where))
        $where .= "{$add_where}";
    }

    $query = "SELECT COUNT(*) FROM {$wpdb->users}{$where}";
    
    return $wpdb->get_var($query);
  }
  
  function is_logged_in_and_visible()
  {
    global $current_user;
    MnglUtils::get_currentuserinfo();

    return (MnglUtils::is_user_logged_in() and MnglUser::user_exists_and_visible($current_user->ID));
  }
  
  function is_logged_in_and_current_user($user_id)
  {
    global $current_user;
    MnglUtils::get_currentuserinfo();

    return (MnglUser::is_logged_in_and_visible() and ($current_user->ID == $user_id));
  }
  
  function is_logged_in_and_an_admin()
  {
    return (MnglUtils::is_user_logged_in() and MnglUser::is_admin());
  }
  
  function is_admin()
  {
    return current_user_can('level_10');
  }

  function user_exists($user_id)
  {
    return MnglUser::get_stored_profile_by_id($id, false);
  }
  
  function user_exists_and_visible($user_id)
  {
    global $mngl_options;
  
    if(MnglUser::get_stored_profile_by_id($user_id))
      return !in_array($user_id, $mngl_options->invisible_users);
    else
      return false;
  }
  
  function screenname_exists($screenname)
  {
    $user = false;

    if(!empty($screenname))
      $user = MnglUser::get_stored_profile_by_screenname($screenname, false);
    
    return ((isset($user->id) and !empty($user->id))?$user->id:false);
  }
  
  function screenname_exists_and_visible($screenname)
  {
    global $mngl_options;
  
    if($user_id = MnglUser::screenname_exists($screenname))
      return !in_array($user_id, $mngl_options->invisible_users);
    else
      return false;
  }
  
  function send_account_notifications($password)
  {
    global $mngl_blogname, $mngl_blogurl, $mngl_options;
    
    $login_link = get_permalink($mngl_options->login_page_id);
    
    if(empty($login_link))
      $login_link = $mngl_blogurl;

    // Send notification email to admin user
    $from_name     = $mngl_blogname; //senders name
    $from_email    = get_option('admin_email'); //senders e-mail address
    $recipient     = "{$from_name} <{$from_email}>"; //recipient
    $header        = "From: {$recipient}"; //optional headerfields
    
    /* translators: In this string, %s is the Blog Name/Title */
    $subject       = sprintf( __("[%s] New User Registration",'mingle'), $mngl_blogname);
    
    /* translators: In this string, %1$s is the blog's name/title, %2$s is the user's real name, %3$s is the user's username, %4$s is the user's email, and %5$s is the user's profile url */
    $message       = sprintf( __( "A new user just joined your community at %1\$s!\n\nName: %2\$s\nUsername: %3\$s\nE-Mail: %4\$s\n\nYou can view this user's profile here: %5\$s", 'mingle' ), $mngl_blogname, $this->full_name, $this->screenname, $this->email, $this->get_profile_url() );
    $message      .= "\n\n";
    $message      .= sprintf( __( "There are now %1\$s Users on %2\$s" , 'mingle'), number_format( (float)$this->get_count() ), $mngl_blogname );
    
    MnglUtils::wp_mail($recipient, $subject, $message, $header);

    // Send password email to new user
    $from_name     = $mngl_blogname; //senders name
    $from_email    = get_option('admin_email'); //senders e-mail address
    $recipient     = "{$this->full_name} <{$this->email}>"; //recipient
    $header        = "From: {$from_name} <{$from_email}>"; //optional headerfields
    
    /* translators: In this string, %s is the Blog Name/Title */
    $subject       = sprintf( __("Welcome to %s!",'mingle'), $mngl_blogname);
    
    /* translators: In this string, %1$s is the user's first name, %2$s is the blog's name/title, %3$s is the user's username, %4$s is the user's password, and %5$s is the blog's URL... */
    $message       = sprintf( __( "%1\$s,\nWelcome to the Community at %2\$s!\n\nUsername: %3\$s\nPassword: %4\$s\nYou can login here: %5\$s\n\nEnjoy!\n\n%2\$s Team", 'mingle' ), (empty($this->first_name)?$this->screenname:$this->first_name), $mngl_blogname, $this->screenname, $password, $login_link );
    
    MnglUtils::wp_mail($recipient, $subject, $message, $header);
  }
  
  function reset_form_key_is_valid($key)
  {
    $stored_key = $this->_get_metadata( 'mngl_reset_password_key' );
    
    return ($stored_key and ($key == $stored_key));
  }
  
  function send_reset_password_requested_notification()
  {
    global $mngl_blogname, $mngl_blogurl, $mngl_options;

    $key = md5(time() . $this->id);
    $this->_update_metadata( 'mngl_reset_password_key', $key );
    
    $permalink = get_permalink($mngl_options->login_page_id);
    $delim     = MnglAppController::get_param_delimiter_char($permalink);
    
    $reset_password_link = "{$permalink}{$delim}action=reset_password&mkey={$key}&mu={$this->screenname}";

    // Send password email to new user
    $from_name     = $mngl_blogname; //senders name
    $from_email    = get_option('admin_email'); //senders e-mail address
    $recipient     = "{$this->full_name} <{$this->email}>"; //recipient
    $header        = "From: {$from_name} <{$from_email}>"; //optional headerfields
    
    /* translators: In this string, %s is the Blog Name/Title */
    $subject       = sprintf( __("[%s] Password Reset",'mingle'), $mngl_blogname);
    
    /* translators: In this string, %1$s is the user's username, %2$s is the blog's name/title, %3$s is the blog's url, %4$s the reset password link */
    $message       = sprintf( __( "Someone requested to reset your password for %1\$s on %2\$s at %3\$s\n\nTo reset your password visit the following address, otherwise just ignore this email and nothing will happen.\n\n%4\$s", 'mingle' ), $this->screenname, $mngl_blogname, $mngl_blogurl, $reset_password_link );
    
    MnglUtils::wp_mail($recipient, $subject, $message, $header);
  }
  
  function set_password_and_send_notifications($key, $password)
  {  
    global $mngl_blogname, $mngl_blogurl, $mngl_options;

    if($this->reset_form_key_is_valid($key))
    {
      $this->_delete_metadata( 'mngl_reset_password_key' );

      //$password = MnglUtils::wp_generate_password( 12, false );
      $this->hashed_password = MnglUtils::wp_hash_password( $password );
      $this->store(true);

      $edit_permalink = get_permalink($mngl_options->profile_edit_page_id);

      // Send notification email to admin user
      $from_name     = $mngl_blogname; //senders name
      $from_email    = get_option('admin_email'); //senders e-mail address
      $recipient     = "{$from_name} <{$from_email}>"; //recipient
      $header        = "From: {$recipient}"; //optional headerfields

      /* translators: In this string, %s is the Blog Name/Title */
      $subject       = sprintf( __("[%s] Password Lost/Changed",'mingle'), $mngl_blogname);

      /* translators: In this string, %1$s is the user's screenname and %2$s is their profile url*/
      $message       = sprintf( __( "Password Lost and Changed for user: %1\$s\nYou can view %1\$s's profile here: %2\$s", 'mingle' ), $this->screenname, $this->get_profile_url() );

      MnglUtils::wp_mail($recipient, $subject, $message, $header);

      $login_link = get_permalink($mngl_options->login_page_id);
      
      // Send password email to new user
      $from_name     = $mngl_blogname; //senders name
      $from_email    = get_option('admin_email'); //senders e-mail address
      $recipient     = "{$this->full_name} <{$this->email}>"; //recipient
      $header        = "From: {$from_name} <{$from_email}>"; //optional headerfields

      /* translators: In this string, %s is the Blog Name/Title */
      $subject       = sprintf( __("[%s] Your new password",'mingle'), $mngl_blogname);

      /* translators: In this string, %1$s is the user's first name, %2$s is the blog's name/title, %3$s is the user's username, %4$s is the user's password, and %5$s is the blog's URL... */
      $message       = sprintf( __( "%1\$s,\n\nYour Password was successfully reset on %2\$s!\n\nUsername: %3\$s\nPassword: %4\$s\n\nYou can login here: %5\$s", 'mingle' ), (empty($this->first_name)?$this->screenname:$this->first_name), $mngl_blogname, $this->screenname, $password, $login_link );

      MnglUtils::wp_mail($recipient, $subject, $message, $header);
      
      return true;
    }
    
    return false;
  }
}
?>
