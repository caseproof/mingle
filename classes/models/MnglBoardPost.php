<?php

class MnglBoardPost
{
  var $table_name;
  
  function MnglBoardPost()
  {
    global $wpdb;
    $this->table_name = "{$wpdb->prefix}mngl_board_posts";
  }
  
  function &get_stored_object()
  { 
    static $this_object;

    if( !isset($this_object) or 
        empty($this_object) or
        !is_object(&$this_object) )
      $this_object =& new MnglBoardPost();
    
    return $this_object;
  }

  function create( $owner_id, $author_id, $message='', $type='post', $source='', $visibility='public' )
  {
    if( $type == 'post' and empty($message) )
      return false;
      
    global $wpdb;
    $query_str = "INSERT INTO {$this->table_name} " . 
                   '(owner_id,'.
                    'author_id,'.
                    'message,'.
                    'type,'.
                    'source,'.
                    'visibility,'.
                    'created_at) ' .
                    'VALUES (%d,%d,%s,%s,%s,%s,NOW())';
    
    $query = $wpdb->prepare( $query_str,
                             $owner_id,
                             $author_id,
                             $message,
                             $type,
                             $source,
                             $visibility );

    $query_results = $wpdb->query($query);

    if($query_results)
    {
      if($type=='post')
        MnglNotification::board_posted($wpdb->insert_id);
      
      return $wpdb->insert_id;
    }
    else
      return false;
  }
  
  function update( $id, $message )
  {
    global $wpdb;
    $query_str = "UPDATE {$this->table_name} " . 
                    'SET message=%s ' .
                    'WHERE id=%d';
    
    $query = $wpdb->prepare( $query_str,
                             $message,
                             $id );

    return $wpdb->query($query);
  }
  
  function delete( $id )
  {
    global $wpdb, $mngl_board_comment;
    
    $mngl_board_comment->delete_all_by_board_post_id( $id );
    MnglBoardPostMeta::delete_all_by_board_post_id( $id );

    $query_str = "DELETE FROM {$this->table_name} WHERE id=%d";
    
    $query = $wpdb->prepare( $query_str,
                             $id );
                             
    return $wpdb->query($query);
  }
  
  function get_one( $id, $include_comments=false )
  {
    global $wpdb;
    
    $query_str = "SELECT *, UNIX_TIMESTAMP(created_at) as created_at_ts FROM {$this->table_name} WHERE id=%d";
    $query = $wpdb->prepare( $query_str, $id);

    $board_post = $wpdb->get_row($query);
    
    if($include_comments)
    {
      global $mngl_board_comment;
      $board_post->comments = $mngl_board_comment->get_all_by_board_post_id( $board_post->id, '', 'created_at ASC' );
    }
    
    return $board_post;
  }
  
  function get_all_public_by_user_id( $owner_id, $include_comments=false, $where='', $order_by='', $limit='' )
  {
    global $mngl_friend, $wpdb, $mngl_options;

    $order_by = ((empty($order_by))?'':" ORDER BY {$order_by}");
    $limit    = ((empty($limit))?'':" LIMIT {$limit}");
    $invisibles = implode(',',$mngl_options->invisible_users);
    $inv_str    = '';
    if(!empty($invisibles))
      $inv_str  = " AND wpmf.user_id NOT IN ({$invisibles})";
    
    $query = "SELECT DISTINCT wpmbp.*, UNIX_TIMESTAMP(wpmbp.created_at) as created_at_ts " .
              "FROM {$this->table_name} wpmbp " .
              "WHERE ( wpmbp.author_id IN (SELECT wpmf.friend_id " . 
                                            "FROM {$mngl_friend->table_name} wpmf " . 
                                            "WHERE wpmf.status = 'verified' " . 
                                              "AND wpmf.user_id={$owner_id}{$inv_str}) " .
                "OR wpmbp.author_id = {$owner_id} ) " .
                "AND wpmbp.visibility='public'" .
              "{$where}{$order_by}{$limit}";
    $query = $wpdb->prepare( $query, $owner_id );

    $board_posts = $wpdb->get_results($query);

    if($include_comments)
    {
      global $mngl_board_comment;
      for( $i = 0; $i < count($board_posts); $i++)
      {
        $board_posts[$i]->comments = $mngl_board_comment->get_all_by_board_post_id( $board_posts[$i]->id, '', 'created_at ASC' );
      }
    }
    
    return $board_posts;
  }
  
  function get_all_by_user_id( $owner_id, $include_comments=false, $where='', $order_by='', $limit='' )
  {
    global $wpdb, $mngl_options;

    $order_by   = ((empty($order_by))?'':" ORDER BY {$order_by}");
    $limit      = ((empty($limit))?'':" LIMIT {$limit}");
    $invisibles = implode(',',$mngl_options->invisible_users);
    $inv_str    = '';
    if(!empty($invisibles))
      $inv_str  = " AND owner_id NOT IN ({$invisibles}) AND author_id NOT IN ({$invisibles})";

    $query_str   = "SELECT *, UNIX_TIMESTAMP(created_at) as created_at_ts FROM {$this->table_name} WHERE owner_id=%d{$inv_str}{$where}{$order_by}{$limit}";
    $query       = $wpdb->prepare( $query_str, $owner_id );
    $board_posts = $wpdb->get_results($query);

    if($include_comments)
    {
      global $mngl_board_comment;
      for( $i = 0; $i < count($board_posts); $i++)
      {
        $board_posts[$i]->comments = $mngl_board_comment->get_all_by_board_post_id( $board_posts[$i]->id, '', 'created_at ASC' );
      }
    }
    
    return $board_posts;
  }
  
  function add_activity_by_id( $owner_id, $author_id, $message_type, $vars='', $visibility='personal' )
  {
    $owner  = MnglUser::get_stored_profile_by_id($owner_id);
    $author = MnglUser::get_stored_profile_by_id($author_id);
    
    if($owner and $author)
      MnglBoardPost::add_activity( $owner, $author, $message_type, $visibility );
  }
  
  function add_activity_by_screenname( $owner_screenname, $author_screenname, $message_type, $vars='', $visibility='personal' )
  {
    $owner  = MnglUser::get_stored_profile_by_screenname($owner_screenname);
    $author = MnglUser::get_stored_profile_by_screenname($author_screenname);
    
    if($owner and $author)
      MnglBoardPost::add_activity( $owner, $author, $message_type, $vars, $visibility );
  }
  
  function add_activity( $owner, $author, $message_type, $vars='', $visibility='personal' )
  { 
    $vars_str = '';

    if(!empty($vars) and is_array($vars))
      $vars_str = serialize($vars);

    $this->create( $owner->id, $author->id, $vars_str, 'activity', $message_type, $visibility );
  }
}
?>
