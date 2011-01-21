<?php

class MnglProfilesController
{
  function MnglProfilesController()
  {
    add_filter('mngl-activity-types', array( &$this, 'add_activity_types' ));
  }
  
  function add_activity_types($activity_types)
  {
    $activity_types['profile_updates'] = array( 'name'    => __('Profile Updates', 'mingle'),
                                                'message' => __('%1$s updated %2$s profile', 'mingle') . '|$owner->screenname, $owner->his_her',
                                                'icon'    => MNGL_URL . "/images/smiley.png" );
    return $activity_types;
  }
  
  function directory($dir_page,$user_search=false,$search_query='',$dir_page_size=25)
  {
    // Reset to page 1 if a search term is entered
    if(!empty($search_query) and $user_search)
      $dir_page = '';
    
    $dir_page = (empty($dir_page)?0:($dir_page-1));
    $dir_offset = $dir_page * $dir_page_size;
    $profiles = MnglUser::get_stored_profiles($search_query,$dir_offset,$dir_page_size);
    
    $search_where = '';
    $search_params = '';

    if(!empty($search_query))
    {
      $search_where = "user_login like '%{$search_query}%'";
      $search_params = "&q={$search_query}";
    }

    $record_count = MnglUser::get_count($search_where);
    $num_pages    = $record_count / $dir_page_size;
    
    $prev_page = $dir_page;
    $next_page = ((($dir_page+1) >= $num_pages)?0:($dir_page + 2));
    require MNGL_VIEWS_PATH . "/mngl-profiles/directory.php";
  }

  function profile($user_screenname='')
  {
    global $mngl_friends_controller, $mngl_boards_controller, $mngl_app_helper, $mngl_blogurl, $mngl_options;

    if( MnglUser::is_logged_in_and_visible() and 
        empty($user_screenname) and
        $user = MnglUser::get_stored_profile())
    {
      $avatar = $user->get_avatar(200);
      require MNGL_VIEWS_PATH . "/mngl-profiles/profile.php";
    }
    else if( !empty($user_screenname) and 
             $user = MnglUser::get_stored_profile_by_screenname($user_screenname) )
    {
      $screenname = $user_screenname;
      $avatar = $user->get_avatar(200);

      require MNGL_VIEWS_PATH . "/mngl-profiles/profile.php";
    }
    else
      require MNGL_VIEWS_PATH . "/shared/unauthorized.php";
  }
  
  function activity()
  {
    global $mngl_friends_controller, $mngl_boards_controller, $mngl_app_helper, $mngl_blogurl;

    if( MnglUser::is_logged_in_and_visible() and 
        $user = MnglUser::get_stored_profile() )
      require MNGL_VIEWS_PATH . "/mngl-profiles/activity.php";
    else
      require MNGL_VIEWS_PATH . "/shared/unauthorized.php";
  }

  function edit()
  {  
    global $mngl_user, $mngl_blogurl;

    $mngl_board_post =& MnglBoardPost::get_stored_object();

    if(MnglUser::is_logged_in_and_visible())
    {
      $avatar_size = 100;
      $avatar = $mngl_user->get_avatar($avatar_size);
      
      if(isset($_POST['action']) and $_POST['action'] == 'process_form')
      {
        $errors = apply_filters('mngl-profile-validate',$mngl_user->validate($_POST,array()));
        
        if(count($errors) <= 0)
        {
          $mngl_user->update($_POST);
          $mngl_user->store();

          $avatar = $mngl_user->get_avatar($avatar_size);
        
          do_action('mngl-profile-update', $mngl_user->id);

          $mngl_board_post->add_activity_by_id( $mngl_user->id, $mngl_user->id, 'profile_updates' );

          require MNGL_VIEWS_PATH . "/mngl-profiles/profile_saved.php";
        }
        else
          require MNGL_VIEWS_PATH . "/shared/errors.php";
      }
      
      require MNGL_VIEWS_PATH . "/mngl-profiles/edit.php";
    }
    else
      require MNGL_VIEWS_PATH . "/shared/unauthorized.php";
  }
  
  function display_status($user_id)
  {
    global $mngl_app_helper;
    
    $user = MnglUser::get_stored_profile_by_id($user_id);

    if($user)
      require MNGL_VIEWS_PATH . "/mngl-profiles/profile_status.php";
  }
  
  function delete_avatar($user_id)
  {
    global $mngl_user;
    
    if(MnglUser::is_logged_in_and_visible() and $user_id==$mngl_user->id)
    {
      $mngl_user->delete_avatars();
      
      $avatar_size = 100;
      $avatar_url  = $mngl_user->get_avatar($avatar_size);
      require MNGL_VIEWS_PATH . "/mngl-profiles/edit_avatar.php";
    }
  }
}
?>
