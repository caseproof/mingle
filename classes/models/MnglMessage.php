<?php
class MnglMessage
{
  var $table_name;
  var $threads_table_name;

  function MnglMessage()
  {
    global $wpdb;
    $this->table_name         = "{$wpdb->prefix}mngl_messages";
    $this->threads_table_name = "{$wpdb->prefix}mngl_threads";

    add_filter('mngl-notification-types', array( $this, 'add_notification_types' ));
  }

  function add_notification_types($notification_types)
  {
    $notification_types['private_message'] = array( 'name'        => __('Private Message', 'mingle'),
                                                    'description' => __('Sent when someone sends you a message', 'mingle') );

    $notification_types['private_message_reply'] = array( 'name'        => __('Private Message Reply', 'mingle'),
                                                          'description' => __('Sent when someone replies to a message you were included on', 'mingle') );

    return $notification_types;
  }

  function create_thread($author_id,$parties,$subject)
  {
    global $wpdb;

    if(empty($subject))
      $subject = __('(no subject)', 'mingle');

    $query_str = "INSERT INTO {$this->threads_table_name} " . 
                   '(author_id, ' .
                   'parties, ' .
                   'subject, ' .
                   'created_at) ' .
                    'VALUES (%d,%s,%s,NOW())';

    $query = $wpdb->prepare( $query_str,
                             $author_id,
                             implode(",",array_unique($parties)),
                             $subject );

    $query_results = $wpdb->query($query);

    if($query_results)
      return $wpdb->insert_id;
    else
      return false;
  }

  function create_message($thread_id, $author_id, $recipient_id, $body, $unread=1)
  {
    global $wpdb;

    $query_str = "INSERT INTO {$this->table_name} " . 
                   '(thread_id, ' .
                    'author_id, ' .
                    'recipient_id, ' .
                    'body, ' .
                    'unread, ' .
                    'created_at) ' .
                    'VALUES (%d,%d,%d,%s,%d,NOW())';
    
    $query = $wpdb->prepare( $query_str,
                             $thread_id,
                             $author_id,
                             $recipient_id,
                             $body,
                             $unread );

    $query_results = $wpdb->query($query);

    if($query_results)
      return $wpdb->insert_id;
    else
      return false;
  }
  
  function get_thread($thread_id)
  {
    global $wpdb;
    $query = "SELECT * FROM {$this->threads_table_name} WHERE id=%d";
    $query = $wpdb->prepare($query, $thread_id);
    return $wpdb->get_row($query);
  }
  
  function get_message($message_id)
  {
    global $wpdb, $mngl_user;
    //TODO: Mark as unread when viewed if mark_as_read is true
    $query = "SELECT *, UNIX_TIMESTAMP(created_at) as created_at_ts FROM {$this->table_name} WHERE id=%d ORDER BY created_at DESC";
    $query = $wpdb->prepare($query, $message_id);
    return $wpdb->get_results($query);
  }
  
  function get_all_messages_by_thread_id($thread_id, $mark_as_read=false)
  {
    global $wpdb, $mngl_user;
    //TODO: Mark as unread when viewed if mark_as_read is true
    $query = "SELECT *, UNIX_TIMESTAMP(created_at) as created_at_ts FROM {$this->table_name} WHERE thread_id=%d AND recipient_id=%d ORDER BY created_at";
    $query = $wpdb->prepare($query, $thread_id, $mngl_user->id);
    return $wpdb->get_results($query);
  }

  function get_all_recieved_messages($pagenum=1,$pagesize=10)
  {
    $messages = $this->get_latest_messages($pagenum,$pagesize);

    $message_array = array();
    foreach($messages as $i => $message)
    {
      $message_array[$i] = array( 'thread' => $this->get_thread( $message->thread_id ),
                                  'latest' => $message );
    }

    return $message_array;
  }
  
  function get_latest_messages($pagenum=1,$pagesize=5)
  {
    global $wpdb, $mngl_user;
    
    $limit = '';
    if($pagenum > 0)
    {
      $offset = ($pagenum - 1) * $pagesize;
      $limit = "LIMIT {$offset},{$pagesize}";
    }

    $query = "SELECT mpm.*, UNIX_TIMESTAMP(mpm.created_at) as created_at_ts
                FROM {$this->table_name} mpm
                WHERE mpm.id=( SELECT mpm2.id 
                                 FROM {$this->table_name} mpm2 
                                 WHERE mpm2.thread_id=mpm.thread_id 
                                   AND mpm2.recipient_id=%d
                                 ORDER BY mpm2.created_at DESC
                                 LIMIT 1 ) 
                ORDER BY mpm.created_at DESC
                {$limit}";
    $query = $wpdb->prepare($query, $mngl_user->id);

    return $wpdb->get_results($query);
  }

  function get_message_count($where='')
  {
    global $wpdb, $mngl_user;

    $query = "SELECT count(*)
                FROM {$this->table_name} mpm
                WHERE mpm.id=( SELECT mpm2.id 
                                 FROM {$this->table_name} mpm2 
                                 WHERE mpm2.thread_id=mpm.thread_id 
                                   AND mpm2.recipient_id=%d
                                 ORDER BY mpm2.created_at DESC
                                 LIMIT 1 )
                  {$where} 
              ORDER BY mpm.created_at DESC";
    $query = $wpdb->prepare($query, $mngl_user->id);

    return $wpdb->get_var($query);
  }

  function get_unread_count()
  {
    return $this->get_message_count('AND mpm.unread=1');
  }
  
  function get_page_count($pagesize=10)
  {
    $message_count = $this->get_message_count();
    return (int)ceil((float)$message_count / (float)$pagesize);
  }
  
  function mark_unread_status_mult_threads($threads, $unread=0)
  {
    global $wpdb, $mngl_user;
    
    if(!is_array($threads) and is_numeric($threads))
      $threads = array($threads);
    
    foreach($threads as $thread_id)
      $this->mark_unread_status($thread_id, $unread);
  }
  
  function mark_unread_status($thread_id, $unread=0)
  {
    global $mngl_user, $wpdb;
    $query = "UPDATE {$this->table_name} SET unread=%d WHERE recipient_id=%d AND thread_id=%d";
    $query = $wpdb->prepare($query, $unread, $mngl_user->id, $thread_id);
    
    $messages = $wpdb->get_results($query);
  }
  
  function delete_messages_mult_threads($threads)
  {
    global $wpdb, $mngl_user;
    
    if(!is_array($threads) and is_numeric($threads))
      $threads = array($threads);
    
    foreach($threads as $thread_id)
      $this->delete_messages($thread_id);
  }
  
  function delete_messages($thread_id)
  {
    global $wpdb, $mngl_user;

    $query = "DELETE FROM {$this->table_name} WHERE recipient_id=%d AND thread_id=%d";
    $query = $wpdb->prepare( $query, $mngl_user->id, $thread_id );
    return $wpdb->query($query);
  }

  function validate($params, $ids, $errors)
  {
    global $mngl_friend, $mngl_user;
    
    extract($params);
    
    //Can't send messages to non-friends or yourself
    foreach($ids as $id)
    {
      if (!$mngl_friend->is_friend($mngl_user->id, $id) && $mngl_user->id != $id && !$mngl_user->is_admin())
        $errors[] = __('You can only send messages to your friends', 'mingle');
    }
    
    $count = count($ids); //Prevents a user from sending a message to themselves
    if(empty($mngl_message_recipients) || $count == 1)
      $errors[] = __('There must be at least one valid recipient', 'mingle');
      
    if(empty($mngl_message_body))
      $errors[] = __('Message can\'t be blank', 'mingle');
    
    if(empty($mngl_message_subject))
      $errors[] = __('Subject can\'t be blank', 'mingle');

    return $errors;
  }
  
  function get_composer_url()
  {
    global $mngl_options;
    
    $url = get_permalink($mngl_options->inbox_page_id);
    
    $param_char = ((preg_match('#\?#',$url))?"&":"?");
    
    return "{$url}{$param_char}action=compose";
  }
  
  function get_message_url($thread_id)
  {
    global $mngl_options;
    
    $url = get_permalink($mngl_options->inbox_page_id);
    
    $param_char = ((preg_match('#\?#',$url))?"&":"?");
    
    return "{$url}{$param_char}action=view&t={$thread_id}";
  }
  
  function get_messages_url()
  {
    global $mngl_options;
    
    return get_permalink($mngl_options->inbox_page_id);
  }

  function message_notification( $to_id, $thread_id, $subject, $body )
  {
    global $mngl_options, $mngl_blogname, $mngl_user;

    $to = MnglUser::get_stored_profile_by_id($to_id);

    if($to)
    {
      $message_url = get_permalink($mngl_options->inbox_page_id);
      $message_url .= ((preg_match('#\?#',$message_url))?"&":"?") . "action=view&t={$thread_id}";

      $opener = sprintf(__('%s sent you a message.', 'mingle'), $mngl_user->screenname );
      $closer = sprintf(__("To reply to this message, follow the link below:\n%s", 'mingle'), $message_url );

      $mail_body =<<<MAIL_BODY
{$opener}

{$subject}

"{$body}"

{$closer}
MAIL_BODY;
      $subject = sprintf(__('%1$s sent you a message on %2$s...', 'mingle'), $mngl_user->screenname, $mngl_blogname); //subject

      MnglNotification::send_notification_email($to, $subject, $mail_body, 'private_message');
    }
  }
  
  function message_reply_notification( $to_id, $thread_id, $subject, $body )
  {
    global $mngl_options, $mngl_blogname, $mngl_user;

    $to = MnglUser::get_stored_profile_by_id($to_id);

    if($to)
    {
      $message_url = get_permalink($mngl_options->inbox_page_id);
      $message_url .= ((preg_match('#\?#',$message_url))?"&":"?") . "u={$to->screenname}&t={$thread_id}";

      $opener = sprintf(__('%s sent a message in reply to a thread.', 'mingle'), $mngl_user->screenname );
      $closer = sprintf(__("To reply to this message, follow the link below:\n%s", 'mingle'), $message_url );

      $mail_body =<<<MAIL_BODY
{$opener}

Re: {$subject}

"{$body}"

{$closer}
MAIL_BODY;
      $subject = sprintf(__('%1$s replied to a thread on %2$s...', 'mingle'), $mngl_user->screenname, $mngl_blogname); //subject

      MnglNotification::send_notification_email($to, $subject, $mail_body, 'private_message_reply');
    }
  }
  
  function get_all_users_if_admin( $where='', $order_by='', $limit='' )
  {
    global $wpdb, $mngl_friend, $user_id;
    
    $where    = ((empty($where))?'':" WHERE {$where}");
    $order_by = ((empty($order_by))?'':" ORDER BY {$order_by}");
    $limit    = ((empty($limit))?'':" LIMIT {$limit}");
    
    $query_str = "SELECT * FROM {$wpdb->users}{$where}{$order_by}{$limit}";

    return $wpdb->get_results($query_str);
  }
}
?>