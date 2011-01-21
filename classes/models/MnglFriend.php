<?php

class MnglFriend
{
  var $table_name;
  var $requests_table_name;
  
  function MnglFriend()
  {
    global $wpdb;
    $this->table_name = "{$wpdb->prefix}mngl_friends";
    $this->requests_table_name = "{$wpdb->prefix}mngl_friend_requests";
  }
  
  function create( $user_id, $friend_id, $status )
  {
    global $wpdb;
    $query_str = "INSERT INTO {$this->table_name} " . 
                   '(user_id,'.
                    'friend_id,'.
                    'status,'.
                    'created_at) ' .
                    'VALUES (%d,%d,%s,NOW())';
    
    $query = $wpdb->prepare( $query_str,
                             $user_id,
                             $friend_id,
                             $status );
                             
    $query_results = $wpdb->query($query);

    if($query_results)
       return $wpdb->insert_id;
     else
       return false;
  }
  
  function create_both( $user_id, $friend_id, $status )
  {
    $return_ids = array();
    $return_ids[] = $this->create( $user_id, $friend_id, $status );
    $return_ids[] = $this->create( $friend_id, $user_id, $status );
    return $return_ids;
  }
  
  function update( $user_id, $friend_id, $status )
  {
    global $wpdb;
    $query_str = "UPDATE {$this->table_name} " . 
                    'SET status=%s ' .
                    'WHERE user_id=%d ' .
                    'AND friend_id=%d';
    
    $query = $wpdb->prepare( $query_str,
                             $status,
                             $user_id,
                             $friend_id );

    return $wpdb->query($query);
  }
  
  function update_both( $user_id, $friend_id, $status )
  {
    $return_statuses = array();
    $return_statuses[] = $this->update( $user_id, $friend_id, $status );
    $return_statuses[] = $this->update( $friend_id, $user_id, $status );
    return $return_ids;
  }
  
  function delete( $id )
  {
    global $wpdb;

    $query_str = "DELETE FROM {$this->table_name} WHERE id=%d";
    
    $query = $wpdb->prepare( $query_str,
                             $id );
                             
    return $wpdb->query($query);
  }
  
  function delete_both( $user_id, $friend_id )
  {
    $both = $this->get_both_by_user_ids( $user_id, $friend_id );
    foreach ($both as $key => $record) {
      $this->delete( $record->id );
    }
  }
  
  function get_one( $id )
  {
    global $wpdb;
    
    $query_str = "SELECT * FROM {$this->table_name} WHERE id=%d";
    $query = $wpdb->prepare( $query_str, $id);

    return $wpdb->get_row($query);
  }
  
  function get_one_by_user_ids( $user_id, $friend_id )
  {
    global $wpdb;
    
    if( MnglUser::user_exists_and_visible($user_id) and
        MnglUser::user_exists_and_visible($friend_id) )
    {
      $query_str = "SELECT * FROM {$this->table_name} WHERE user_id=%d AND friend_id=%d";
      $query = $wpdb->prepare( $query_str, $user_id, $friend_id );
      
      return $wpdb->get_row($query);
    }
    else
      return false;
  }
  
  function get_both_by_user_ids( $user_id, $friend_id )
  {
    global $wpdb;
    $return_rows = array();
    $user   = $this->get_one_by_user_ids( $user_id, $friend_id );
    $friend = $this->get_one_by_user_ids( $friend_id, $user_id );
    
    if($user and $friend)
    {
      $return_rows[] = $this->get_one_by_user_ids( $user_id, $friend_id );
      $return_rows[] = $this->get_one_by_user_ids( $friend_id, $user_id );

      return $return_rows;
    }
    else
      return false;
  }
  
  function get_all_ids_by_user_id( $user_id, $where='', $order_by='', $limit='' )
  {
    global $wpdb, $mngl_options;
    
    $where    = ((empty($where))?'':" AND {$where}");
    $order_by = ((empty($order_by))?'':" ORDER BY {$order_by}");
    $limit    = ((empty($limit))?'':" LIMIT {$limit}");
    $invisibles = implode(',',$mngl_options->invisible_users);

    $inv_str    = '';
    if(!empty($invisibles))
      $inv_str  = " AND user_id NOT IN ({$invisibles}) AND friend_id NOT IN ({$invisibles})";
    
    $query_str = "SELECT friend_id FROM {$this->table_name} WHERE user_id=%d{$inv_str}{$where}{$order_by}{$limit}";
    $query = $wpdb->prepare( $query_str, $user_id );

    return $wpdb->get_col($query,0);
  }
  
  function get_all_by_user_id( $user_id, $where='', $order_by='', $limit='' )
  {
    global $wpdb, $mngl_friend, $mngl_options;
    
    if(in_array($user_id,$mngl_options->invisible_users))
      return false;
    
    $where    = ((empty($where))?'':" AND {$where}");
    $order_by = ((empty($order_by))?'':" ORDER BY {$order_by}");
    $limit    = ((empty($limit))?'':" LIMIT {$limit}");
    $invisibles = implode(',',$mngl_options->invisible_users);

    $inv_str    = '';
    if(!empty($invisibles))
      $inv_str  = " AND wpu.ID NOT IN ({$invisibles})";

    $query = "SELECT wpmf.*, wpu.* FROM {$mngl_friend->table_name} wpmf JOIN {$wpdb->users} wpu ON wpmf.friend_id=wpu.ID WHERE wpmf.user_id={$user_id}{$inv_str}{$where}{$order_by}{$limit}";

    return $wpdb->get_results($query);
  }
  
  function get_friends_user_array( $user_id, $where='', $offset=0, $limit=50, $order_by='' )
  {
    global $wpdb;
    
    $where = ((!empty($where))?" AND ".$where:'');

    if( MnglFriend::get_friend_count( $user_id, "status='verified'{$where}" ) <= 0 )
      return false;

    if(empty($limit) or !$limit)
      $limit_stmt = '';
    else
      $limit_stmt = "{$offset},{$limit}";
      
    $friends = MnglFriend::get_all_by_user_id( $user_id, "status='verified'{$where}", $order_by, $limit_stmt );

    $friends_a = array();
    
    foreach ($friends as $friend) 
    {
      $new_friend  = MnglUser::get_stored_profile_by_id($friend->friend_id);

      if($new_friend)
        $friends_a[] = $new_friend;
    }
    
    return $friends_a;
  }
  
  function get_friend_count( $user_id, $where='' )
  {
    global $wpdb, $mngl_friend, $mngl_options;
    
    $where = ((empty($where))?'':" AND {$where}");
    $invisibles = implode(',',$mngl_options->invisible_users);

    $inv_str    = '';
    if(!empty($invisibles))
      $inv_str  = " AND wpu.ID NOT IN ({$invisibles})";

    $query = "SELECT COUNT(*) FROM {$mngl_friend->table_name} wpmf JOIN {$wpdb->users} wpu ON wpmf.friend_id=wpu.ID WHERE wpmf.user_id={$user_id}{$inv_str}{$where}";

    return $wpdb->get_var($query);
  }
  
  function already_requested($user_id, $friend_id)
  {
    return MnglFriend::is_friend($user_id, $friend_id, 'pending');
  }
  
  function is_friend($user_id, $friend_id, $status='verified')
  {
    // Just bail if this is self
    if($user_id == $friend_id)
      return false;

    // cache the results so we don't have to make extra calls to the db
    static $is_friend_array;
    
    if( !isset($is_friend_array) or 
        empty($is_friend_array) or 
        !is_array($is_friend_array))
      $is_friend_array = array();
      
    if( !isset($is_friend_array[$user_id]) or 
        empty($is_friend_array[$user_id]) or 
        !is_array($is_friend_array[$user_id]))
      $is_friend_array[$user_id] = array();
    
    
    if( !isset($is_friend_array[$friend_id]) or 
        empty($is_friend_array[$friend_id]) or 
        !is_array($is_friend_array[$friend_id]))
      $is_friend_array[$friend_id] = array();

    if( !isset($is_friend_array[$user_id][$friend_id]) )
    {
      global $wpdb, $mngl_friend;

      $query = "SELECT id FROM {$mngl_friend->table_name} WHERE user_id=%d AND friend_id=%d AND status=%s";
      $query = $wpdb->prepare($query, $user_id, $friend_id, $status);

      $is_friend_array[$user_id][$friend_id] = $wpdb->get_var($query);
      $is_friend_array[$friend_id][$user_id] = $is_friend_array[$user_id][$friend_id];
    }

    return $is_friend_array[$user_id][$friend_id];
  }
  
  function is_self($user_id, $friend_id)
  {
    return ($user_id == $friend_id); 
  }
  
  function accept_friend($request_id)
  {
    $request = $this->get_friend_request($request_id);
    
    $this->update_both( $request->user_id, $request->friend_id, 'verified');
    
    MnglNotification::friendship_verified($request->friend_id, $request->user_id);
  }
  
  function ignore_friend($request_id)
  {
    $request = $this->get_friend_request($request_id);
    
    $this->delete_both( $request->user_id, $request->friend_id );
    
    $this->delete_request( $request_id );
  }
  
  function delete_request( $request_id )
  {
    global $wpdb;

    $query_str = "DELETE FROM {$this->requests_table_name} WHERE id=%d";
    
    $query = $wpdb->prepare( $query_str,
                             $request_id );
                             
    return $wpdb->query($query);
  }
  
  function get_friend_request( $request_id )
  {
    global $wpdb;
    
    $query_str = "SELECT * FROM {$this->requests_table_name} WHERE id=%d";
    $query = $wpdb->prepare( $query_str, $request_id );

    return $wpdb->get_row($query);
  }
  
  function get_friend_requests( $user_id )
  {
    global $wpdb;
    
    $query_str = "SELECT * FROM {$this->requests_table_name} WHERE friend_id=%d";
    $query = $wpdb->prepare( $query_str, $user_id );

    return $wpdb->get_results($query);
  }
  
  function get_friend_requests_count( $user_id )
  {
    global $wpdb;
    
    $query_str = "SELECT COUNT(*) FROM {$this->requests_table_name} WHERE friend_id=%d";
    $query = $wpdb->prepare( $query_str, $user_id );

    return $wpdb->get_var($query);
  }
  
  function request_friend( $user_id, $friend_id )
  {  
    global $wpdb;

    if(!$this->is_self($user_id,$friend_id) and
       !$this->is_friend($user_id,$friend_id) and
       !$this->already_requested($user_id,$friend_id))
    {   
      $ids = $this->create_both( $user_id, $friend_id, 'pending' );

      $query_str = "INSERT INTO {$this->requests_table_name} " . 
                     '(user_id,'.
                      'friend_id,'.
                      'friend_record_a_id,'.
                      'friend_record_b_id,'.
                      'created_at) ' .
                      'VALUES (%d,%d,%d,%d,NOW())';
      
      $query = $wpdb->prepare( $query_str,
                               $user_id,
                               $friend_id,
                               $ids[0],
                               $ids[1] );

      $query_results = $wpdb->query($query);
      
      if($query_results)
      {
        MnglNotification::friendship_requested($user_id, $friend_id);
        
        return $wpdb->insert_id;
      }
      else
        return false;
    }
  }
  
  function can_friend($user_id, $friend_id)
  {
    return apply_filters('mngl-can-friend', true, $user_id, $friend_id);
  }
  
  function can_delete_friend($user_id, $friend_id)
  {
    return apply_filters('mngl-can-delete-friend', true, $user_id, $friend_id);
  }
}
?>
