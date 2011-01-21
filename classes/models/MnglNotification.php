<?php
class MnglNotification
{
  function MnglNotification()
  {
    add_filter('mngl-notification-types', array( &$this, 'add_notification_types' ));
  }
  
  function add_notification_types($notification_types)
  {
    $notification_types['friend_request']      = array( 'name'        => __('Friendship Request', 'mingle'),
                                                        'description' => __('Sent when someone requests you as a friend', 'mingle') );
    $notification_types['friend_verification'] = array( 'name'        => __('Friendship Verification', 'mingle'),
                                                        'description' => __('Sent when a friendship has been confirmed', 'mingle') );
    $notification_types['board_post']          = array( 'name'        => __('Board Post', 'mingle'),
                                                        'description' => __('Sent when someone posts to your Board', 'mingle') );
    $notification_types['board_comment']       = array( 'name'        => __('Board Comment', 'mingle'),
                                                        'description' => __("Sent when someone posts to one of your Board Posts or on a comment thread that you've participated in", 'mingle') );
    $notification_types['tagged_in_post']      = array( 'name'        => __('Tagged In a Post', 'mingle'),
                                                        'description' => __("Sent when someone tags you in a Board post", 'mingle') );

    $notification_types['tagged_in_comment']   = array( 'name'        => __('Tagged In a Comment', 'mingle'),
                                                        'description' => __("Sent when someone tags you in a Board comment", 'mingle') );
    
    return $notification_types;
  }
  
  /** Send notification that someone has requested your friendship
    */
  function friendship_requested($requestor_id, $friend_id)
  {
    global $mngl_options, $mngl_blogname, $mngl_blogurl;
    
    $requestor = MnglUser::get_stored_profile_by_id($requestor_id);
    $friend    = MnglUser::get_stored_profile_by_id($friend_id);

    if($requestor and $friend)
    {
      $friend_requests_url = get_permalink($mngl_options->friend_requests_page_id);
      
      $opener = sprintf(__('%1$s has requested you as a friend on %2$s!', 'mingle'), $requestor->screenname, $mngl_blogname);
      $closer = sprintf(__('Please visit %s to Accept or Ignore this request.', 'mingle'), $friend_requests_url );

      $mail_body =<<<MAIL_BODY
{$opener}

{$closer}
MAIL_BODY;
      $subject = sprintf(__('%1$s wants to be your Friend on %2$s', 'mingle'), $requestor->screenname, $mngl_blogname); //subject
      
      MnglNotification::send_notification_email($friend, $subject, $mail_body, 'friend_request');
    }
  }
  
  /** Send notification that the friendship has been verified/confirmed
    */
  function friendship_verified($verifier_id, $requestor_id)
  {
    global $mngl_blogname;
    
    $requestor = MnglUser::get_stored_profile_by_id($requestor_id);
    $verifier  = MnglUser::get_stored_profile_by_id($verifier_id);
    
    if($requestor and $verifier)
    {
      $requestor_profile_url = $requestor->get_profile_url();
      $verifier_profile_url = $verifier->get_profile_url();
      
/*** Notify the requestor ***/
      $opener = sprintf(__('You\'re now friends with %1$s on %2$s!', 'mingle'), $verifier->screenname, $mngl_blogname);
      $closer = sprintf(__('Visit %1$s to see %2$s profile.', 'mingle'), $verifier_profile_url, $verifier->his_her);

      $mail_body =<<<MAIL_BODY
{$opener}

{$closer}
MAIL_BODY;

      $subject = sprintf(__('%1$s Has Confirmed Your Friend Request on %2$s', 'mingle'), $verifier->screenname, $mngl_blogname); //subject
      MnglNotification::send_notification_email($requestor, $subject, $mail_body, 'friend_verification');

/*** Notify the verifier ***/
      $opener = sprintf(__('You\'re now friends with %1$s on %2$s!', 'mingle'), $requestor->screenname, $mngl_blogname);
      $closer = sprintf(__("Visit %s to see %s profile.", 'mingle'), $requestor_profile_url, $requestor->his_her);

      $mail_body =<<<MAIL_BODY
{$opener}

{$closer}
MAIL_BODY;
    
      $subject = sprintf(__('You\'re Now Friends With %1$s on %2$s', 'mingle'), $requestor->screenname, $mngl_blogname); //subject
      MnglNotification::send_notification_email($verifier, $subject, $mail_body, 'friend_verification');
    }
  }
  
  /** Send notification that the your board was posted to
    */
  function board_posted($board_post_id)
  {
    global $mngl_blogname, $mngl_options;
    
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    
    $board_post = $mngl_board_post->get_one($board_post_id);
    
    $owner  = MnglUser::get_stored_profile_by_id( $board_post->owner_id );
    $author = MnglUser::get_stored_profile_by_id( $board_post->author_id );
    
    if( $board_post->owner_id != $board_post->author_id )
    {
      if($owner and $author)
      {
        $owner_profile_url = get_permalink($mngl_options->profile_page_id);
        
        $opener = sprintf(__('%1$s posted this on your Board at %2$s', 'mingle'), $author->screenname, $mngl_blogname);
        $closer = sprintf(__("Visit %s to see your Board", 'mingle'), $owner_profile_url);
        $mail_body =<<<MAIL_BODY
{$opener}:

"{$board_post->message}"

{$closer}.
MAIL_BODY;
        $subject = sprintf(__('%1$s Posted to Your Board on %2$s', 'mingle'), $author->screenname, $mngl_blogname); //subject
        MnglNotification::send_notification_email($owner, $subject, $mail_body, 'board_post');
      }
    }
    
    $tagged_users =& MnglBoardsHelper::get_tagged_users($board_post->message);
    
    if($tagged_users and is_array($tagged_users))
    {
      foreach ($tagged_users as $key => $tagged_user)
      {
        if( $tagged_user->id != $owner->id and 
            $tagged_user->id != $author->id )
        {
          $opener = sprintf(__("You were tagged in %1\$s's post on %2\$s's Board at %3\$s", 'mingle'), $author->screenname, $owner->screenname, $mngl_blogname);
          $closer = sprintf(__("Visit %s to see the post you were tagged in.", 'mingle'), MnglBoardsHelper::board_post_url($board_post->id));
          $mail_body =<<<MAIL_BODY
{$opener}:

"{$board_post->message}"

{$closer}.
MAIL_BODY;
          $subject = sprintf(__('You were tagged in a Post on %s', 'mingle'), $mngl_blogname); //subject
          MnglNotification::send_notification_email($tagged_user, $subject, $mail_body, 'tagged_in_post');
        }
      }
    }
  }

  /** Send notification that the your board post or a board post you've commented on was commented on
    */
  function board_commented($board_comment_id)
  {
    global $mngl_blogname, $mngl_board_comment;
    
    $mngl_board_post =& MnglBoardPost::get_stored_object();
    
    $board_comment = $mngl_board_comment->get_one($board_comment_id);
    $board_post    = $mngl_board_post->get_one($board_comment->board_post_id);
    $owner         = MnglUser::get_stored_profile_by_id($board_post->owner_id);
    $post_author   = MnglUser::get_stored_profile_by_id($board_post->author_id);
    $author        = MnglUser::get_stored_profile_by_id($board_comment->author_id);

    if($owner and $author)
    {
      $comments      = $mngl_board_comment->get_all_by_board_post_id($board_post->id);
      $commentor_ids = array();
      $commentors    = array();
      $owner_profile_url = $owner->get_profile_url();
      
      foreach ($comments as $comment)
      {
        if( $comment->author_id != $owner->id and
            $comment->author_id != $author->id and
            $comment->author_id != $post_author->id and
            !in_array($comment->author_id, $commentor_ids))
        {
          $commentor_ids[] = $comment->author_id;
          $curr_commentor  = MnglUser::get_stored_profile_by_id($comment->author_id);

          if($curr_commentor)
            $commentors[] = $curr_commentor;
        }
      }
      
      /*** Send notification to board owner ***/      
      if($owner->id != $author->id)
      {
        $opener = sprintf(__('%1$s commented on your Board Post on %2$s', 'mingle'), $author->screenname, $mngl_blogname);
        $closer = sprintf(__('View this comment from %1$s\'s Board at %2$s.', 'mingle'), $owner->screenname, MnglBoardsHelper::board_post_url($board_post->id));

        $mail_body =<<<MAIL_BODY
{$opener}:

"{$board_comment->message}"

{$closer}
MAIL_BODY;
        $subject = sprintf(__('%1$s commented on a Post on your Board on %2$s', 'mingle'), $author->screenname, $mngl_blogname); //subject
      
        MnglNotification::send_notification_email($owner, $subject, $mail_body, 'board_comment');
      }
        
      /*** Send notification to the board post author ***/
      if( $owner->id != $post_author->id and
          $post_author->id != $author->id )
      {
        $opener = sprintf(__('%1$s commented on your Post on %2$s\'s Board on %3$s', 'mingle'), $author->screenname, $owner->screenname, $mngl_blogname);
        $closer = sprintf(__('View this comment from %1$s\'s Board at %2$s.', 'mingle'), $owner->screenname, MnglBoardsHelper::board_post_url($board_post->id));

        $mail_body =<<<MAIL_BODY
{$opener}:

"{$board_comment->message}"

{$closer}
MAIL_BODY;
        $subject = sprintf(__('%1$s commented on your Post on %2$s\'s Board on %3$s', 'mingle'), $author->screenname, $owner->screenname, $mngl_blogname); //subject

        MnglNotification::send_notification_email($post_author, $subject, $mail_body, 'board_comment');
      }
      
      /*** Send notification to other commentors ***/
      $opener = sprintf(__('%1$s commented on %2$s\'s Board Post', 'mingle'), $author->screenname, $owner->screenname);
      $closer = sprintf(__('View this comment from %1$s\'s Board at %2$s.', 'mingle'), $owner->screenname, MnglBoardsHelper::board_post_url($board_post->id));
      
      $mail_body =<<<MAIL_BODY
{$opener}:

"{$board_comment->message}"

{$closer}
MAIL_BODY;

      $subject = sprintf(__('%1$s commented on %2$s\'s Board Post on %3$s', 'mingle'), $author->screenname, $owner->screenname, $mngl_blogname);

      foreach ($commentors as $commentor)
        MnglNotification::send_notification_email($commentor, $subject, $mail_body, 'board_comment');

      /*** Send notification to users tagged in the comment ***/
      $tagged_users =& MnglBoardsHelper::get_tagged_users($board_comment->message);
      
      if($tagged_users and is_array($tagged_users))
      {
        foreach ($tagged_users as $key => $tagged_user)
        {
          if( $tagged_user->id != $owner->id and 
              $tagged_user->id != $author->id and
              $tagged_user->id != $post_author->id and
              !in_array($tagged_user->id, $commentor_ids) )
          {
            $opener = sprintf(__("You were tagged in %1\$s's comment on %2\$s's Board at %3\$s", 'mingle'), $author->screenname, $owner->screenname, $mngl_blogname);
          $closer = sprintf(__("Visit %s to see the comment you were tagged in.", 'mingle'), MnglBoardsHelper::board_post_url($board_post->id));
          $mail_body =<<<MAIL_BODY
{$opener}:

"{$board_comment->message}"

{$closer}.
MAIL_BODY;
            $subject = sprintf(__('You were tagged in a comment on %s', 'mingle'), $mngl_blogname); //subject
            MnglNotification::send_notification_email($tagged_user, $subject, $mail_body, 'tagged_in_comment');
          }
        }
      }
    }
  }
  
  function send_notification_email_by_screenname($screenname, $subject, $message, $message_type)
  {
    $user = MnglUser::get_stored_profile_by_screenname($screenname);
    
    if($user)
      MnglNotification::send_notification_email($user, $subject, $message, $message_type);
  }
  
  function send_notification_email($user, $subject, $message, $message_type)
  {
    global $mngl_blogname, $mngl_options;
    
    if(isset($user->hide_notifications[$message_type]))
      return;
    
    $from_name     = $mngl_blogname; //senders name
    $from_email    = get_option('admin_email'); //senders e-mail address
    $recipient     = "{$to_name} <{$to_email}>"; //recipient
    $header        = "From: {$from_name} <{$from_email}>"; //optional headerfields
    $subject       = html_entity_decode(strip_tags(stripslashes($subject)));
    $message       = html_entity_decode(strip_tags(stripslashes($message)));
    $signature     = MnglNotification::get_mail_signature();
    
    $to_email      = $user->email;
    $to_name       = $user->screenname;

    if( $mngl_options->mailer['type'] == 'smtp' )
      $full_to_email = $to_email;
    else
      $full_to_email = "{$to_name} <{$to_email}>";
    
    MnglUtils::wp_mail($full_to_email, $subject, $message.$signature, $header);
    
    do_action('mngl_notification', $user, $subject, $message.$signature);
  }
  
  function get_mail_signature()
  {
    global $mngl_options, $mngl_blogname;
    
    $admin_email = get_option('admin_email');
    $settings_url = get_permalink($mngl_options->profile_edit_page_id);
    
    $thanks              = __('Thanks!', 'mingle');
    $team                = sprintf(__('%s Team', 'mingle'), $mngl_blogname);
    $manage_subscription = sprintf(__('If you want to stop future emails like this from coming to you, please modify your notification settings at %1$s or contact the system administrator at %2$s.', 'mingle'), $settings_url, $admin_email);

    $signature =<<<MAIL_SIGNATURE


{$thanks}

{$team}

------

{$manage_subscription}
MAIL_SIGNATURE;

    return $signature;
  }
}
?>
