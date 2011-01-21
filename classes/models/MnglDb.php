<?php
class MnglDb
{
  var $friends;
  var $friend_requests;
  var $board_posts;
  var $board_comments;
  var $board_post_metas;
  var $custom_fields;
  var $custom_field_values;
  var $custom_field_options;
  var $threads;
  var $messages;
  var $message_metas;
  
  function MnglDb()
  {
    global $wpdb;

    $this->friends              = "{$wpdb->prefix}mngl_friends";
    $this->friend_requests      = "{$wpdb->prefix}mngl_friend_requests";
    $this->board_posts          = "{$wpdb->prefix}mngl_board_posts";
    $this->board_comments       = "{$wpdb->prefix}mngl_board_comments";
    $this->board_post_metas     = "{$wpdb->prefix}mngl_board_post_metas";
    $this->custom_fields        = "{$wpdb->prefix}mngl_custom_fields";
    $this->custom_field_values  = "{$wpdb->prefix}mngl_custom_field_values";
    $this->custom_field_options = "{$wpdb->prefix}mngl_custom_field_options";
    $this->threads              = "{$wpdb->prefix}mngl_threads";
    $this->messages             = "{$wpdb->prefix}mngl_messages";
    $this->message_metas        = "{$wpdb->prefix}mngl_message_metas";
  }
  
  function upgrade()
  {
    global $wpdb;
    
    $db_version = 11; // this is the version of the database we're moving to
    $old_db_version = get_option('mngl_db_version');

    if($db_version != $old_db_version)
    {
      $this->before_upgrade($old_db_version);

      $charset_collate = '';
      if( $wpdb->has_cap( 'collation' ) )
      {
        if( !empty($wpdb->charset) )
          $charset_collate = "DEFAULT CHARACTER SET $wpdb->charset";
        if( !empty($wpdb->collate) )
          $charset_collate .= " COLLATE $wpdb->collate";
      }
      
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      
      /* Create/Upgrade Friends Table */
      $sql = "CREATE TABLE {$this->friends} (
                id int(11) NOT NULL auto_increment,
                user_id int(11) NOT NULL,
                friend_id int(11) NOT NULL,
                status varchar(255) DEFAULT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY user_id (user_id),
                KEY friend_id (friend_id),
                KEY status (status)
              ) {$charset_collate};";
      
      dbDelta($sql);
      
      /* Create/Upgrade Friend Requests Table */
      $sql = "CREATE TABLE {$this->friend_requests} (
                id int(11) NOT NULL auto_increment,
                user_id int(11) NOT NULL,
                friend_id int(11) NOT NULL,
                friend_record_a_id int(11) NOT NULL,
                friend_record_b_id int(11) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY user_id (user_id),
                KEY friend_id (friend_id),
                KEY friend_record_a_id (friend_record_a_id),
                KEY friend_record_b_id (friend_record_b_id)
              ) {$charset_collate};";
      
      dbDelta($sql);
      
      /* Create/Upgrade Board Posts Table */
      $sql = "CREATE TABLE {$this->board_posts} (
                id int(11) NOT NULL auto_increment,
                owner_id int(11) NOT NULL,
                author_id int(11) NOT NULL,
                message text DEFAULT NULL,
                type varchar(255) DEFAULT 'post',
                source varchar(255) DEFAULT NULL,
                visibility varchar(255) DEFAULT 'public',
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY owner_id (owner_id),
                KEY author_id (author_id),
                KEY type (type),
                KEY source (source),
                KEY visibility (visibility)
              ) {$charset_collate};";
      
      dbDelta($sql);
      
      /* Create/Upgrade Board Comments Table */
      $sql = "CREATE TABLE {$this->board_comments} (
                id int(11) NOT NULL auto_increment,
                author_id int(11) NOT NULL,
                message text DEFAULT NULL,
                board_post_id int(11) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY author_id (author_id),
                KEY board_post_id (board_post_id)
              ) {$charset_collate};";
      
      dbDelta($sql);
      
      /* Create/Upgrade Board Posts Table */
      $sql = "CREATE TABLE {$this->board_post_metas} (
                id int(11) NOT NULL auto_increment,
                meta_key varchar(255) default NULL,
                meta_value longtext default NULL,
                board_post_id int(11) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY board_post_id (board_post_id)
              ) {$charset_collate};";
      
      dbDelta($sql);

      /***** CUSTOM FIELDS TABLES *****/
      /* Create/Upgrade Fields Table */
      $sql = "CREATE TABLE {$this->custom_fields} (
                id int(11) NOT NULL auto_increment,
                name varchar(255) NOT NULL,
                type varchar(255) NOT NULL,
                default_value text DEFAULT NULL,
                on_signup tinyint(1) NOT NULL DEFAULT 0,
                visibility varchar(255) NOT NULL,
                PRIMARY KEY  (id),
                KEY type (type),
                KEY visibility (visibility)
              ) {$charset_collate};";
    
      dbDelta($sql);

      /* Create/Upgrade Field Values Table */
      $sql = "CREATE TABLE {$this->custom_field_values} (
                id int(11) NOT NULL auto_increment,
                field_id int(11) NOT NULL,
                user_id int(11) NOT NULL,
                value varchar(255) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY field_id (field_id),
                KEY user_id (user_id),
                KEY created_at (created_at)
              ) {$charset_collate};";
    
      dbDelta($sql);

      /* Create/Upgrade Field Values Table */
      $sql = "CREATE TABLE {$this->custom_field_options} (
                id int(11) NOT NULL auto_increment,
                field_id int(11) NOT NULL,
                value varchar(255) NOT NULL,
                label varchar(255) NOT NULL,
                PRIMARY KEY  (id),
                KEY field_id (field_id)
              ) {$charset_collate};";
      
      dbDelta($sql);

      /***** PRIVATE MESSAGING TABLES *****/
      /* Create/Upgrade Threads Table */
      $sql = "CREATE TABLE {$this->threads} (
                id int(11) NOT NULL auto_increment,
                author_id int(11) NOT NULL,
                parties text NOT NULL,
                subject text NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY author_id (author_id),
                KEY created_at (created_at)
              ) {$charset_collate};";
    
      dbDelta($sql);

      /* Create/Upgrade Messages Table */
      $sql = "CREATE TABLE {$this->messages} (
                id int(11) NOT NULL auto_increment,
                thread_id int(11) NOT NULL,
                author_id int(11) NOT NULL,
                recipient_id int(11) NOT NULL,
                body text NOT NULL,
                unread tinyint(1) NOT NULL DEFAULT 1,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY thread_id (thread_id),
                KEY author_id (author_id),
                KEY recipient_id (recipient_id),
                KEY created_at (created_at)
              ) {$charset_collate};";
    
      dbDelta($sql);

      /* Create/Upgrade Board Posts Table */
      $sql = "CREATE TABLE {$this->message_metas} (
                id int(11) NOT NULL auto_increment,
                meta_key varchar(255) default NULL,
                meta_value longtext default NULL,
                message_id int(11) NOT NULL,
                created_at datetime NOT NULL,
                PRIMARY KEY  (id),
                KEY message_id (message_id)
              ) {$charset_collate};";
    
      dbDelta($sql);

      $this->after_upgrade($old_db_version);
    }
    
    /***** SAVE DB VERSION *****/
    update_option('mngl_db_version',$db_version);
  }
  
  function before_upgrade($curr_db_version)
  {
    // Nothing here yet
  }
  
  function after_upgrade($curr_db_version)
  {
    if(isset($curr_db_version) and !empty($curr_db_version))
    {
      if($curr_db_version < 4)
      {
        global $mngl_options;
        
        if($mngl_options->display_name_type == 'fullname')
        {
          global $wpdb;

          $query = "SELECT ID FROM {$wpdb->users}";
          $user_ids = $wpdb->get_col($query,0);
          
          foreach ($user_ids as $user_id)
          {
            $profile = MnglUser::get_stored_profile_by_id($user_id);
            if($profile)
            {
              if( isset($profile->first_name) and !empty($profile->first_name) and
                  isset($profile->last_name) and !empty($profile->last_name) )
              {
                $profile->full_name = "{$profile->first_name} {$profile->last_name}";
                $profile->store(true);
              }
              else if( isset($profile->first_name) and !empty($profile->first_name) )
              {
                $profile->full_name = $profile->first_name;
                $profile->store(true);
              }
              else if( isset($profile->last_name) and !empty($profile->last_name) )
              {
                $profile->full_name = $profile->last_name;
                $profile->first_name = $profile->last_name;
                $profile->store(true);
              }
            }
          }
        }
      }
      
      // Change avatars to just be file names only
      if($curr_db_version < 9)
      {
        global $wpdb;
        
        $query = "SELECT * FROM {$wpdb->usermeta} WHERE meta_key=%s";
        $query = $wpdb->prepare( $query, 'mngl_avatar' );
        $avatars = $wpdb->get_results($query);
        
        foreach( $avatars as $avatar )
        {
          $basename_avatar = basename(ABSPATH . $avatar->meta_value);

          if($basename_avatar != $avatar->meta_value)
          {
            $query = "UPDATE {$wpdb->usermeta} SET meta_value=%s WHERE umeta_id=%d";
            $query = $wpdb->prepare( $query, $basename_avatar, $avatar->umeta_id );
            $wpdb->query( $query );
          }
          
          $user =& MnglUser::get_stored_profile_by_id( $avatar->user_id );
          if(isset($user->avatars) and !empty($user->avatars) and is_array($user->avatars))
          {
            $new_avatars = array();
            foreach($user->avatars as $type_name => $type)
            {
              $new_avatars[$type_name] = array();
              foreach($type as $sized_name => $sized)
              {
                $basename_sized = basename(ABSPATH . $sized);

                $new_avatars[$type_name][$sized_name] = $basename_sized;
              }
            }
            
            if(!empty($new_avatars))
            {
              $query = "UPDATE {$wpdb->usermeta} SET meta_value=%s WHERE user_id=%d and meta_key=%s";
              $query = $wpdb->prepare( $query, serialize($new_avatars), $avatar->user_id, 'mngl_avatars' );
              $wpdb->query( $query );
            }
          }
        }
      }
    }
  }

  function create_record($table, $args, $record_created_at=true)
  {
    global $wpdb;
  
    $cols = array();
    $vars = array();
    $values = array();
  
    $i = 0;
    foreach($args as $key => $value)
    {  
      $cols[$i] = $key;
      if(is_numeric($value))
        $vars[$i] = '%d';
      else
        $vars[$i] = '%s';
      $values[$i] = $value;
      $i++;
    }
  
    if($record_created_at)
    {
      $cols[$i] = 'created_at';
      $vars[$i] = 'NOW()';
    }
  
    if(empty($cols))
      return false;
  
    $cols_str = implode(',',$cols);
    $vars_str = implode(',',$vars);
  
    $query = "INSERT INTO {$table} ( {$cols_str} ) VALUES ( {$vars_str} )";
    $query = $wpdb->prepare( $query, $values );

    $query_results = $wpdb->query($query);
  
    if($query_results)
      return $wpdb->insert_id;
    else
      return false;
  }
  
  function update_record( $table, $id, $args )
  {
    global $wpdb;
  
    if(empty($args) or empty($id))
      return false;
  
    $set = '';
    $values = array();
    foreach($args as $key => $value)
    {
      if(empty($set))
        $set .= ' SET';
      else
        $set .= ',';
  
      $set .= " {$key}=";
  
      if(is_numeric($value))
        $set .= "%d";
      else
        $set .= "%s";
  
      $values[] = $value;
    }
  
    $values[] = $id;
    $query = "UPDATE {$table}{$set} WHERE id=%d";
  
    $query = $wpdb->prepare( $query, $values );
  
    return $wpdb->query($query);
  }
  
  function delete_records($table, $args)
  {
    global $wpdb;
    extract(MnglDb::get_where_clause_and_values( $args ));

    $query = "DELETE FROM {$table}{$where}";
    $query = $wpdb->prepare($query, $values);

    return $wpdb->query($query);
  }
  
  function get_count($table, $args=array())
  {
    global $wpdb;
    extract(MnglDb::get_where_clause_and_values( $args ));
    
    $query = "SELECT COUNT(*) FROM {$table}{$where}";
    $query = $wpdb->prepare($query, $values);
    return $wpdb->get_var($query);
  }
  
  function get_where_clause_and_values( $args )
  {
    $where = '';
    $values = array();
    foreach($args as $key => $value)
    {
      if(!empty($where))
        $where .= ' AND';
      else
        $where .= ' WHERE';
  
      $where .= " {$key}=";
  
      if(is_numeric($value))
        $where .= "%d";
      else
        $where .= "%s";
  
      $values[] = $value;
    }
    
    return compact('where','values');
  }
  
  function get_one_record($table, $args=array())
  {
    global $wpdb;

    extract(MnglDb::get_where_clause_and_values( $args ));

    $query = "SELECT * FROM {$table}{$where} LIMIT 1";
    $query = $wpdb->prepare($query, $values);
    return $wpdb->get_row($query);
  }
  
  function get_records($table, $args=array(), $order_by='', $limit='')
  {
    global $wpdb;

    extract(MnglDb::get_where_clause_and_values( $args ));
  
    if(!empty($order_by))
      $order_by = " ORDER BY {$order_by}";
  
    if(!empty($limit))
      $limit = " LIMIT {$limit}";
  
    $query = "SELECT * FROM {$table}{$where}{$order_by}{$limit}";
    $query = $wpdb->prepare($query, $values);
    return $wpdb->get_results($query);
  }
}
?>