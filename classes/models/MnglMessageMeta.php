<?php
class MnglMessageMeta
{  
  function get($message_id,$meta_key,$return_var=false)
  {
    global $wpdb, $mngl_db;
    $query_str = "SELECT meta_value FROM {$this->message_metas} WHERE meta_key=%s and message_id=%d";
    $query = $wpdb->prepare($query_str,$meta_key,$message_id);
    
    if($return_var)
      return $wpdb->get_var("{$query} LIMIT 1");
    else
      return $wpdb->get_col($query, 0);
  }

  function add($message_id, $meta_key, $meta_value)
  {
    global $wpdb, $mngl_db;

    $query_str = "INSERT INTO {$this->message_metas} " .
                 '(meta_key,meta_value,message_id,created_at) VALUES (%s,%s,%d,NOW())';
    $query = $wpdb->prepare($query_str, $meta_key, $meta_value, $message_id);
    return $wpdb->query($query);
  }

  function update($message_id, $meta_key, $meta_values)
  {
    global $wpdb, $mngl_db;
    MnglMessageMeta::delete($message_id, $meta_key);

    if(!is_array($meta_values))
      $meta_values = array($meta_values);

    $status = false;
    foreach($meta_values as $meta_value)
      $status = MnglMessageMeta::add($message_id, $meta_key, $meta_value);

    return $status;
  }

  function delete($message_id, $meta_key)
  {
    global $wpdb, $mngl_db;

    $query_str = "DELETE FROM {$this->message_metas} " .
                 "WHERE meta_key=%s AND message_id=%d";
    $query = $wpdb->prepare($query_str, $meta_key, $message_id);
    return $wpdb->query($query);
  }
}
?>