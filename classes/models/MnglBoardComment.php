<?php

class MnglBoardComment
{
  var $table_name;
  
  function MnglBoardComment()
  {
    global $wpdb;
    $this->table_name = "{$wpdb->prefix}mngl_board_comments";
  }
  
  function create( $board_post_id, $author_id, $message )
  {
    if(empty($message))
      return false;

    global $wpdb;
    $query_str = "INSERT INTO {$this->table_name} " . 
                   '(board_post_id,'.
                    'author_id,'.
                    'message,'.
                    'created_at) ' .
                    'VALUES (%d,%d,%s,NOW())';
    
    $query = $wpdb->prepare( $query_str,
                             $board_post_id,
                             $author_id,
                             $message );
                             
    $query_results = $wpdb->query($query);

    if($query_results)
    {
      MnglNotification::board_commented($wpdb->insert_id);
      
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
    global $wpdb;

    $query_str = "DELETE FROM {$this->table_name} WHERE id=%d";
    
    $query = $wpdb->prepare( $query_str,
                             $id );
                             
    return $wpdb->query($query);
  }
  
  function delete_all_by_board_post_id( $id )
  {
    global $wpdb;

    $query_str = "DELETE FROM {$this->table_name} WHERE board_post_id=%d";
    
    $query = $wpdb->prepare( $query_str,
                             $id );
                             
    return $wpdb->query($query);
  }
  
  function get_one( $id )
  {
    global $wpdb;
    
    $query_str = "SELECT *, UNIX_TIMESTAMP(created_at) as created_at_ts FROM {$this->table_name} WHERE id=%d";
    $query = $wpdb->prepare( $query_str, $id);

    return $wpdb->get_row($query);
  }
  
  function get_all_by_board_post_id( $board_post_id, $where='', $order_by='', $limit='' )
  {
    global $wpdb, $mngl_options;
    
    $where      = ((empty($where))?'':" AND {$where}");
    $order_by   = ((empty($order_by))?'':" ORDER BY {$order_by}");
    $limit      = ((empty($limit))?'':" LIMIT {$limit}");
    $invisibles = implode(',',$mngl_options->invisible_users);
    
    $inv_str    = '';
    if(!empty($invisibles))
      $inv_str  = " AND author_id NOT IN ({$invisibles})";
    
    $query_str = "SELECT *, UNIX_TIMESTAMP(created_at) as created_at_ts FROM {$this->table_name} WHERE board_post_id=%d{$inv_str}{$where}{$order_by}{$limit}";
    $query = $wpdb->prepare( $query_str, $board_post_id );

    return $wpdb->get_results($query);
  }
  
}
?>