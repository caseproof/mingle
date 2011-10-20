<?php

class MnglFriendsController
{
  function MnglFriendsController()
  {  
    add_filter('mngl-activity-types', array( &$this, 'add_activity_types' ));
    add_action('user_register', array(&$this, 'add_default_friends') );
  }
  
  function add_activity_types($activity_types)
  {
    $activity_types['friendship_confirmation'] = array( 'name'    => 'Friend Confirmation',
                                                        'message' => __('%1$s is now friends with %2$s', 'mingle') . '|$owner->screenname, $author_profile_link',
                                                        'icon'    => MNGL_URL . "/images/smiley.png" );
    return $activity_types;
  }

  function list_friends($dir_page,$user_param,$user_search=false,$search_query='',$dir_page_size=25)
  {
    global $mngl_friends_controller, $mngl_friend, $mngl_user, $mngl_blogurl, $mngl_options;

    if(!empty($search_query))
      $dir_page = '';

    if(MnglUser::is_logged_in_and_visible() and (!isset($user_param) OR empty($user_param) OR !$user_param))
    {
      $user = $mngl_user;
      $page_params = '';
    }
    else if( isset($user_param) and 
             !empty($user_param) and
             $user = MnglUser::get_stored_profile_by_screenname($user_param) )
      $page_params = '&mu=' . $user_param;
    else
    {
      require MNGL_VIEWS_PATH . "/shared/unauthorized.php";
      return;
    }
    
    $user_avatar = $user->get_avatar(75);
    
    $where_clause = (!empty($search_query)?"user_login LIKE '%{$search_query}%'":'');
    $where_clause_wn = (!empty($search_query)?" AND {$where_clause}":'');

    $dir_page = (empty($dir_page)?0:($dir_page-1));
    $dir_offset = $dir_page * $dir_page_size;
    $friends = MnglFriend::get_friends_user_array($user->id, $where_clause, $dir_offset,$dir_page_size);

    $record_count = MnglFriend::get_friend_count($user->id, "status='verified'{$where_clause_wn}", $search_query );
    $num_pages = $record_count / $dir_page_size;

    $prev_page = $dir_page;
    $next_page = ((($dir_page+1) >= $num_pages)?0:($dir_page + 2));
    
    $friends_page_url = get_permalink($mngl_options->friend_page_id);
    $param_char = ((preg_match("#\?#",$friends_page_url))?'&':'?');
  
    require MNGL_VIEWS_PATH . "/mngl-friends/list.php";
  }
  
  function list_friend_requests()
  {  
    global $current_user, $mngl_friend;
    if(MnglUser::is_logged_in_and_visible())
    {
      MnglUtils::get_currentuserinfo();
    
      $requests = $mngl_friend->get_friend_requests($current_user->ID);
    
      require MNGL_VIEWS_PATH . "/mngl-friends/requests.php"; 
    }
    else
      require MNGL_VIEWS_PATH . "/shared/unauthorized.php";
  }
  
  function accept_friend($request_id)
  {
    if(MnglUser::is_logged_in_and_visible())
    {
      global $mngl_friend;
      
      $mngl_board_post =& MnglBoardPost::get_stored_object();
      
      $mngl_friend->accept_friend($request_id);
      
      $request = $mngl_friend->get_friend_request($request_id);
      
      if( MnglFriend::can_friend($request->user_id, $request->friend_id) )
      {
        // Put an activity entry on both boards...
        $mngl_board_post->add_activity_by_id( $request->user_id, $request->friend_id, 'friendship_confirmation' );
        $mngl_board_post->add_activity_by_id( $request->friend_id, $request->user_id, 'friendship_confirmation' );
        
        $mngl_friend->delete_request( $request_id );
      }
    }
  }
  
  function ignore_friend($request_id)
  {
    if(MnglUser::is_logged_in_and_visible())
    {
      global $mngl_friend;
      
      $mngl_friend->ignore_friend($request_id, $current_user);
    }
  }
  
  function display_add_friend_button($user_id, $friend_id)
  {
    if( MnglUser::is_logged_in_and_visible() and 
        MnglFriend::can_friend($user_id, $friend_id))
    {
      global $current_user, $mngl_friend;
    
      if($user_id == $friend_id)
      {
        require MNGL_VIEWS_PATH . "/mngl-friends/me.php";
        return;
      }
    
      $friend = $mngl_friend->get_one_by_user_ids($user_id, $friend_id);
  
      if($friend)
      {
        if($friend->status == 'verified')
          require MNGL_VIEWS_PATH . "/mngl-friends/already_friend.php";
        else 
          require MNGL_VIEWS_PATH . "/mngl-friends/friend_requested.php";
      }
      else
      {
        if(  MnglUser::user_exists_and_visible($user_id) and
             MnglUser::user_exists_and_visible($friend_id) )
          require MNGL_VIEWS_PATH . "/mngl-friends/add_button.php";
      }
    }
  }
  
  function friend_request($user_id,$friend_id)
  {
    global $mngl_user, $mngl_friend;
    
    if($mngl_user->is_logged_in_and_current_user($user_id) and MnglFriend::can_friend($user_id, $friend_id))
      return $mngl_friend->request_friend( $user_id, $friend_id );
  }
  
  function delete_friend($user_id,$friend_id)
  {
    global $mngl_user, $mngl_friend;
    
    if($mngl_user->is_logged_in_and_current_user($user_id) and
       MnglFriend::can_delete_friend($user_id, $friend_id))
      return $mngl_friend->delete_both($user_id,$friend_id);
  }
  
  function display_friends_grid($user_id,$cols=3,$rows=2)
  {
    global $mngl_friend;
    
    $grid_cell_count = $cols * $rows;
    $user_count = $mngl_friend->get_friend_count($user_id, "status='verified'");
    $owner = MnglUser::get_stored_profile_by_id($user_id);
    if($owner)
    {
      $user_type = __('Friends', 'mingle');
      $all_users_url = $owner->get_friends_url();
      
      // Grab a random selection of friends from the database
      $users = $mngl_friend->get_friends_user_array( $user_id, '', 0, $grid_cell_count, 'RAND()' );
      require MNGL_VIEWS_PATH . "/shared/user_grid.php";
    }
  }
  
  /** Add default friends to all existing users. */
  function add_default_friends_to_all_users()
  {  
    if(MnglUser::is_logged_in_and_an_admin())
    {
      $users = MnglUser::get_all();

      foreach($users as $user)
        $this->add_default_friends($user->ID);
    }
  }
  
  function add_default_friends($user_id)
  {
    global $mngl_options, $mngl_friend;

    if(count($mngl_options->default_friends) > 0)
    {   
      foreach($mngl_options->default_friends as $friend_id)
      {
        $friend_id = (int)$friend_id;
        if( $friend_id and
            !empty($friend_id) and
            ( $user_id != $friend_id ) and
            !$mngl_friend->is_friend($user_id,$friend_id) 
            )
        {
          $mngl_friend->create_both($user_id, $friend_id, 'verified');
        }
      }
    }
  }
}
?>
