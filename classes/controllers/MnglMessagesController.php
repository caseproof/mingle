<?php

class MnglMessagesController
{
  function MnglMessagesController()
  {
    add_action('mngl-profile-display', array( $this, 'display_message_button' ), 1);
    //add_action('mngl-profile-list-name-display', array( $this, 'display_message_button'), 1);
  }

  function display_composer()
  {
    global $mngl_user;
    
    if (isset($_GET['mu']) && !empty($_GET['mu']))
    {
      $to_id = $_GET['mu'];
      $curr_user =& MnglUser::get_stored_profile_by_id($to_id);
      $to = $curr_user->screenname.", ";
    }

    require( MNGL_VIEWS_PATH . "/mngl-messages/composer.php" );
  }
  
  function display_messages($pagenum=1,$scrub_values=false)
  {
    global $mngl_message, $mngl_user, $mngl_options;
    if( MnglUser::is_logged_in_and_visible() )
    {
      $page_count = $mngl_message->get_page_count();

      if( !isset($pagenum) or 
          empty($pagenum) or 
          ($pagenum > $page_count) )
        $pagenum = 1;

      $messages = $mngl_message->get_all_recieved_messages($pagenum);
      
      if($scrub_values)
      {
        unset($_POST['action']);
        unset($_POST['mngl_message_subject']);
        unset($_POST['mngl_message_body']);
        unset($_POST['mngl_message_recipients']);
      }
      
      $permalink = get_permalink($mngl_options->inbox_page_id);
      $param_char = MnglAppController::get_param_delimiter_char($permalink);

      $prev_page = (int)$pagenum - 1;
      $next_page = (($pagenum < $page_count)?((int)$pagenum + 1):0);

      require( MNGL_VIEWS_PATH . "/mngl-messages/list.php" );
    }
    else
      require MNGL_VIEWS_PATH . "/shared/unauthorized.php";
  }
  
  function display_message_button($user_id)
  {
    global $mngl_options, $mngl_friend, $mngl_user;
    
    if( MnglUtils::is_user_logged_in() and
        MnglUser::user_exists_and_visible($mngl_user->id) and
        $mngl_friend->is_friend($mngl_user->id, $user_id))
    {
      $user = MnglUser::get_stored_profile_by_id($user_id);
      
      $permalink = get_permalink($mngl_options->inbox_page_id);
      $param_char = MnglAppController::get_param_delimiter_char($permalink);
      
      require( MNGL_VIEWS_PATH . "/mngl-messages/button.php" );
    }
  }
  
  function display_message($thread_id)
  {
    global $mngl_message;
    
    if( MnglUser::is_logged_in_and_visible() )
    {
      $messages = $mngl_message->get_all_messages_by_thread_id($thread_id, true);
      $thread   = $mngl_message->get_thread($thread_id);
      
      $mngl_message->mark_unread_status($thread_id, false);
      
      require( MNGL_VIEWS_PATH . "/mngl-messages/view.php" );
    }
    else
      require MNGL_VIEWS_PATH . "/shared/unauthorized.php";
  }
  
  function display_single_message($message)
  {
    $author = MnglUser::get_stored_profile_by_id($message->author_id);
    $avatar = $author->get_avatar(48);
    $body = MnglBoardsHelper::format_message($message->body, false);
    
    require( MNGL_VIEWS_PATH . "/mngl-messages/single.php" );
  }
  
  function create_message($user_id, $subject, $body, $parties)
  {
    global $mngl_message, $mngl_user;
    
    $ids = array();
    $screennames = explode(',',preg_replace('#,$#','',$parties));
    
    if(is_array($screennames))
    {
      foreach($screennames as $screenname)
      {
        $curr_user =& MnglUser::get_stored_profile_by_screenname($screenname);
        
        if($curr_user and is_object($curr_user))
          $ids[] = $curr_user->id;
      }
  
      $ids[] = $user_id;
      $ids = array_unique($ids); // Remove any duplicates
    }

    $errors = $mngl_message->validate($_POST, $ids, array());

    if(empty($errors))
    {
      if($this->send_initial_message( $mngl_user->id, $ids, $subject, $body ))
        require( MNGL_VIEWS_PATH . "/mngl-messages/message_sent.php" );
      $this->display_messages(1,true);
    }
    else
    {
      require( MNGL_VIEWS_PATH . "/shared/errors.php" );
      $this->display_messages();
    }
  }
  
  function create_reply($thread_id, $body)
  {
    global $mngl_message, $mngl_user;

    if(!empty($body) and $message_id = $this->send_message( $thread_id, $mngl_user->id, $body, "reply" ))
    {
      $message = new stdClass;
      $message->author_id     = $mngl_user->id;
      $message->body          = $body;
      $message->created_at_ts = time();

      $this->display_single_message($message);
    }
    
    return '';
  }
  
  function delete_thread($thread_id)
  {
    global $mngl_message;
    
    $mngl_message->delete_messages($thread_id);
  }

  function delete_threads($threads)
  {
    global $mngl_message;
    
    $threads = explode(',', $threads);
    $mngl_message->delete_messages_mult_threads($threads);
  }

  function mark_unread_statuses($threads, $unread=0)
  {
    global $mngl_message;
    
    $threads = explode(',', $threads);
    $mngl_message->mark_unread_status_mult_threads($threads, $unread);
  }
  
  function send_initial_message( $author_id, $parties, $subject, $body )
  {
    global $mngl_message;
    
    if(is_array($parties))
    {
      $thread_id = $mngl_message->create_thread($author_id, $parties, $subject);
    
      if($thread_id)
        return $this->send_message( $thread_id, $author_id, $body );
    }

    return false;
  }
  
  
  function send_message($thread_id, $author_id, $body, $type="message" )
  {
    global $mngl_message, $mngl_user;

    $thread = $mngl_message->get_thread($thread_id);
    
    $parties = explode(',',$thread->parties);
    
    if(is_array($parties) and !empty($parties))
    {
      foreach($parties as $recipient_id)
      {
        $unread = (($author_id == $recipient_id)?0:1);
        $mngl_message->create_message($thread_id, $author_id, $recipient_id, $body, $unread);
      
        if( $recipient_id != $mngl_user->id )
        {
          if($type=="message")
            $mngl_message->message_notification( $recipient_id, $thread_id, $thread->subject, $body );
          else if($type=="reply")
            $mngl_message->message_reply_notification( $recipient_id, $thread_id, $thread->subject, $body );
        }
      }
      
      return true;
    }
    else
     return false;
  }
  
  function lookup_friends($query_str,$output='json')
  {
    global $mngl_user, $mngl_friend, $mngl_utils, $mngl_message;

    if($output=='json')
    {
      if ($mngl_user->is_admin()) //Allows admins to message all users regardless of friendship status
        $friends_array = $mngl_message->get_all_users_if_admin("user_login LIKE '%{$query_str}%' AND user_login <> '{$mngl_user->screenname}'");
      else
        $friends_array = $mngl_friend->get_all_by_user_id( $mngl_user->id, "status='verified' AND user_login LIKE '%{$query_str}%'" );
      $fmt_friends_array = array();
      foreach($friends_array as $friend)
      {
        $avatar = get_avatar($friend->ID, 20);
        $screenname = $friend->user_login;
        $higlighted_screenname = preg_replace("#(" . preg_quote($query_str) . ")#", '<strong>$1</strong>', $screenname);
        
        $fmt_friends_array[] = array("id" => $friend->ID, "value" => $screenname, "label" => "<span class='mngl_friend_dropdown_item'>{$avatar}&nbsp;<span class='mngl_friend_dropdown_text'>{$higlighted_screenname}</span></span>");
      }
      echo MnglAppHelper::json_encode($fmt_friends_array);
    }
  }
}
?>
