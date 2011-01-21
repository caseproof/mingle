<?php

class MnglBoardsController
{
  function MnglBoardsController()
  {
    add_filter('mngl-activity-types', array( &$this, 'add_activity_types' ));
  }
  
  function add_activity_types($activity_types)
  {
    $activity_types['board_posts'] = array( 'name'         => __('Board Posts', 'mingle'),
                                            'message'      => __('%1$s posted to %2$s\'s board', 'mingle') . '|$owner->screenname, $author_profile_link',
                                            'icon'         => MNGL_URL . '/images/board_post.png' );
    $activity_types['board_comments'] = array( 'name'         => __('Board Comments', 'mingle'),
                                               'message'      => __('%1$s commented on %2$s\'s post', 'mingle') . '|$owner->screenname, $author_profile_link',
                                               'icon'         => MNGL_URL . '/images/board_comment.png' );
    return $activity_types;
  }

  function display($user_id, $page=1, $page_size=15)
  {
    global $mngl_options, $mngl_friend, $mngl_user, $mngl_app_helper, $mngl_board_comment, $current_user;
    
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    
    $page_start_index = (($page-1)*$page_size);
    
    $owner_id  = $user_id;
    $author_id = $mngl_user->id;
    
    $user = MnglUser::get_stored_profile_by_id($user_id);

    if($user)
    {
      $messages = "'" . implode("','",array_keys($mngl_options->activity_types)) . "'";
      $board_posts = $mngl_board_post->get_all_by_user_id( $user_id, true, " AND (type='post' or (type='activity' and source IN ({$messages})))", 'created_at DESC', "{$page_start_index},{$page_size}" );
      
      if($page==1) // only show the status on the first page
        require MNGL_VIEWS_PATH . "/mngl-profiles/profile_status.php";
      require MNGL_VIEWS_PATH . "/mngl-boards/display.php";
    }
  }
  
  function activity_display($user_id, $page=1, $page_size=15)
  {
    global $mngl_friend, $mngl_user, $mngl_app_helper, $mngl_board_comment, $current_user;
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    
    $page_start_index = (($page-1)*$page_size);
    
    $owner_id  = $user_id;
    $author_id = $mngl_user->id;
    $public = true;
    
    $board_posts = $mngl_board_post->get_all_public_by_user_id( $user_id, true, '', 'created_at DESC', "{$page_start_index},{$page_size}" );
    
    require MNGL_VIEWS_PATH . "/mngl-boards/display.php";
  }
  
  /** This displays a board post with all it's comments */
  function display_board_post($board_post, $public=false)
  {
    global $mngl_options, $mngl_friend, $mngl_user, $mngl_app_helper, $mngl_board_comment, $current_user, $mngl_boards_controller;
    
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    
    $author_id = $mngl_user->id;
    $author = MnglUser::get_stored_profile_by_id($board_post->author_id);
    $owner  = MnglUser::get_stored_profile_by_id($board_post->owner_id);
    
    if($author and $owner)
    {
      if(isset($_GET['mbpost']))
      {
        if($owner->privacy == 'public' or 
                  ( MnglUser::is_logged_in_and_visible() and 
                    ( ($mngl_user->id == $owner->id) or 
                      ($mngl_user->id == $author->id) or 
                      $mngl_friend->is_friend($mngl_user->id,$owner->id) or
                      $mngl_friend->is_friend($mngl_user->id,$author->id))))
          require MNGL_VIEWS_PATH . "/mngl-boards/board_post.php";
        else
        {
          $user =& $owner;
          require( MNGL_VIEWS_PATH . '/mngl-boards/private.php' );
        }
        return;
      }
      else
        require MNGL_VIEWS_PATH . "/mngl-boards/board_post.php";
    }
  }

  function display_comment($comment, $public=false, $comment_hidden_class='')
  {
    global $mngl_options, $mngl_friend, $mngl_user, $mngl_app_helper, $mngl_board_comment, $current_user;
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    $author_id = $mngl_user->id;
    require MNGL_VIEWS_PATH . "/mngl-boards/board_comment.php";
  }
  
  function clear_status($user_id)
  {
    global $mngl_user;
    
    if( MnglUser::is_logged_in_and_visible() and $mngl_user->id == $user_id )
      $mngl_user->clear_status();
  }

  function post($owner_id, $author_id, $message, $public=false)
  {
    global $mngl_friend, $mngl_user;
    
    $mngl_board_post =& MnglBoardPost::get_stored_object();

    if( MnglUser::is_logged_in_and_visible() and
        ( ($owner_id==$author_id) or
          $mngl_friend->is_friend($owner_id, $author_id) ) )
    {
      $board_post_id = $mngl_board_post->create($owner_id, $author_id, strip_tags(MnglBoardsHelper::escape_code_blocks(stripslashes(MnglAppHelper::decode_unicode($message)))));

      if(isset($board_post_id) and !empty($board_post_id) and is_numeric($board_post_id))
        do_action('mngl_post_to_board', $board_post_id);
    }
      
    if( MnglUser::is_logged_in_and_visible() and ($owner_id==$author_id) )
      $mngl_user->update_status(strip_tags(MnglBoardsHelper::escape_code_blocks(stripslashes(MnglAppHelper::decode_unicode($message)))));
    
    if( MnglUser::is_logged_in_and_visible() and ($owner_id!=$author_id) )
      $mngl_board_post->add_activity_by_id( $author_id, $owner_id, 'board_posts' );

    if($public)
      $this->activity_display($owner_id);
    else
      $this->display($owner_id);
  }
  
  function comment($board_post_id, $author_id, $message, $public=false)
  {
    global $mngl_friend, $mngl_board_comment, $mngl_user;
    
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    
    $board_post = $mngl_board_post->get_one($board_post_id);
    
    if( MnglUser::is_logged_in_and_visible() and
        ( ($board_post->owner_id == $author_id) or
          ($board_post->author_id == $author_id) or
          $mngl_friend->is_friend($author_id, $board_post->owner_id) or 
          $mngl_friend->is_friend($author_id, $board_post->author_id) ) )
      $comment_id = $mngl_board_comment->create($board_post_id, $author_id, strip_tags(MnglBoardsHelper::escape_code_blocks(stripslashes(MnglAppHelper::decode_unicode($message)))));

    if( MnglUser::is_logged_in_and_visible() and 
        ( $board_post->owner_id != $author_id ) and
        ( $board_post->author_id != $author_id ) )
      $mngl_board_post->add_activity_by_id( $author_id, $board_post->author_id, 'board_comments' );
    
    $comment = $mngl_board_comment->get_one($comment_id);
    $board_post = $mngl_board_post->get_one($comment->board_post_id, true);
    
    $this->display_comment($comment, $public);
    $this->display_comment_form( $mngl_user->id, $board_post, $public, true );
  }
  
  function delete_post($board_post_id, $public=false)
  {
    global $mngl_user;
    
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    
    $board_post = $mngl_board_post->get_one($board_post_id);

    if( $mngl_user->is_logged_in_and_current_user($board_post->owner_id) or
        $mngl_user->is_logged_in_and_current_user($board_post->author_id) or
        $mngl_user->is_logged_in_and_an_admin())
      $mngl_board_post->delete($board_post_id);

    if($public)
      $this->activity_display($board_post->owner_id);
    else
      $this->display($board_post->owner_id);
  }
  
  function delete_comment($board_comment_id, $public=false)
  {
    global $mngl_board_comment, $mngl_user;
    
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    
    $board_comment = $mngl_board_comment->get_one($board_comment_id);
    $board_post = $mngl_board_post->get_one($board_comment->board_post_id);

    if( $mngl_user->is_logged_in_and_current_user($board_post->owner_id) or
        $mngl_user->is_logged_in_and_current_user($board_comment->author_id) or
        $mngl_user->is_logged_in_and_an_admin())
      $mngl_board_comment->delete($board_comment_id);

    if($public)
      $this->activity_display($mngl_user->id);
    else
      $this->display($board_post->owner_id);
  }
  
  function display_comment_form( $author_id, $board_post, $public=false, $show_fake_form=true )
  {
    global $mngl_friend;
    $author = MnglUser::get_stored_profile_by_id($author_id);

    if($author)
    {
      $board_post_id = $board_post->id;

      $avatar = $author->get_avatar(36);

      require( MNGL_VIEWS_PATH . "/mngl-boards/comment_form.php" );
    }
  }
  
  function show_older_posts($screenname,$page,$location)
  {
    $user = false;
    if(!empty($screenname))
      $user = MnglUser::get_stored_profile_by_screenname($screenname);

    if($user)
    {
      if($location=='boards')
        $this->display($user->id, $page);
      else if($location=='activity')
        $this->activity_display($user->id, $page);
    }
  }
}
?>
