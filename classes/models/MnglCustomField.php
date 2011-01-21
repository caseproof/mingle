<?php
class MnglCustomField
{  
  var $table_name;
  var $values_table_name;
  var $options_table_name;

  function MnglCustomField()
  {
    global $wpdb;

    $this->table_name         = "{$wpdb->prefix}mngl_custom_fields";
    $this->values_table_name  = "{$wpdb->prefix}mngl_custom_field_values";
    $this->options_table_name = "{$wpdb->prefix}mngl_custom_field_options";
  }
  
  function create_field($name, $type, $default_value='', $visibility='public', $on_signup=0 )
  {
    global $wpdb;

    $query = "INSERT INTO {$this->table_name} " . 
                   '(name, ' .
                   'type, ' .
                   'default_value, ' .
                   'visibility, ' .
                   'on_signup ) ' .
                    'VALUES (%s,%s,%s,%s,%d)';

    $query = $wpdb->prepare( $query,
                             $name,
                             $type,
                             $default_value,
                             $visibility,
                             $on_signup );

    $query_results = $wpdb->query($query);

    if($query_results)
      return $wpdb->insert_id;
    else
      return false;
  }

  function update_field($id, $name, $type, $default_value='', $visibility='public', $on_signup=0)
  {
    global $wpdb, $prli_url_utils;

    $query = "UPDATE {$this->table_name} " . 
                    'SET name=%s, ' .
                        'type=%s, ' .
                        'default_value=%s, ' .
                        'visibility=%s, '.
                        'on_signup=%d ' .
                   ' WHERE id=%d';

    $query = $wpdb->prepare( $query,
                             $name,
                             $type,
                             $default_value,
                             $visibility,
                             $on_signup,
                             $id );

    $query_results = $wpdb->query($query);
    return $query_results;   
  }
  
  function delete_field($id)
  {
    global $wpdb;
    
    $this->delete_all_options_by_field_id($id);
    $this->delete_all_values_by_field_id($id);

    $query = "DELETE FROM {$this->table_name} WHERE id=%d";
    $query = $wpdb->prepare( $query, $id );
    
    return $wpdb->query($query);
  }

  function create_value($value, $field_id, $user_id)
  {
    global $wpdb;

    $query = "INSERT INTO {$this->values_table_name} " . 
                   '(value, ' .
                   'field_id, ' .
                   'user_id, ' .
                   'created_at) ' .
                    'VALUES (%s,%s,%s,NOW())';

    $query = $wpdb->prepare( $query,
                             $value,
                             $field_id,
                             $user_id );

    $query_results = $wpdb->query($query);

    if($query_results)
      return $wpdb->insert_id;
    else
      return false;
  }

  function update_value($id, $value)
  {
    global $wpdb;

    $query = "UPDATE {$this->values_table_name} " . 
                    'SET value=%s ' .
                   ' WHERE id=%d';

    $query = $wpdb->prepare( $query,
                             $value,
                             $id );

    $query_results = $wpdb->query($query);
    return $query_results;
  }
  
  function delete_value($id)
  {
    global $wpdb;
    
    $query = "DELETE FROM {$this->values_table_name} WHERE id=%d";
    $query = $wpdb->prepare( $query, $id );
    
    return $wpdb->query($query);
  }
  
  function delete_all_values_by_field_id($field_id)
  {
    global $wpdb;
    
    $query = "DELETE FROM {$this->values_table_name} WHERE field_id=%d";
    $query = $wpdb->prepare( $query, $field_id );
    
    return $wpdb->query($query);
  }

  function create_option($value, $label, $field_id)
  {
    global $wpdb;

    $query = "INSERT INTO {$this->options_table_name} " . 
                   '(value, ' .
                   'label, ' .
                   'field_id) ' .
                    'VALUES (%s,%s,%d)';

    $query = $wpdb->prepare( $query,
                             $value,
                             $label,
                             $field_id );

    $query_results = $wpdb->query($query);

    if($query_results)
      return $wpdb->insert_id;
    else
      return false;
  }

  function update_option($id, $value, $label)
  {
    global $wpdb;

    $query = "UPDATE {$this->options_table_name} " . 
                    'SET value=%s, ' .
                        'label=%s' .
                   ' WHERE id=%d';

    $query = $wpdb->prepare( $query,
                             $value,
                             $label,
                             $id );

    $query_results = $wpdb->query($query);
    return $query_results;
  }
  
  function delete_option($id)
  {
    global $wpdb;
    
    $query = "DELETE FROM {$this->options_table_name} WHERE id=%d";
    $query = $wpdb->prepare( $query, $id );
    
    return $wpdb->query($query);
  }
  
  function delete_all_options_by_field_id($field_id)
  {
    global $wpdb;
    
    $query = "DELETE FROM {$this->options_table_name} WHERE field_id=%d";
    $query = $wpdb->prepare( $query, $field_id );
    
    return $wpdb->query($query);
  }
  
  function update_fields_from_array($fields)
  {
    if(!is_array($fields) or empty($fields))
      return;

    $db_field_ids = $this->get_all_ids();

    $field_ids = array();
    $fields_by_id = array();
    $create_fields = array();
    foreach($fields as $field)
    {
      if(isset($field['id']) and !empty($field['id']) and is_numeric($field['id']))
      {
        $field_ids[] = $field['id'];
        $fields_by_id[$field['id']] = $field;
      }
      else
        $create_fields[] = $field;
    }

    // CREATE fields
    foreach ($create_fields as $field)
    {
      $field_id = $this->create_field( $field['name'],
                                       $field['type'],
                                       $field['default_value'],
                                       $field['visibility'],
                                       (int)( isset($field['on_signup']) and !empty($field['on_signup']) ) );

      $this->update_field_options_from_array($field_id, $field['options']);
    }

    // DELETE or UPDATE fields
    foreach($db_field_ids as $db_id)
    {
      if(in_array($db_id, $field_ids))
      {
        $db_field = $fields_by_id[$db_id];
        $this->update_field( $db_field['id'], 
                             $db_field['name'],
                             $db_field['type'],
                             $db_field['default_value'],
                             $db_field['visibility'],
                             (int)( isset($db_field['on_signup']) and !empty($db_field['on_signup']) ) );
      }
      else
        $this->delete_field($db_id);
      
      $this->update_field_options_from_array($db_id, $db_field['options']);
    }

  }
  
  function update_field_options_from_array($field_id, $options)
  { 
    if(!is_array($options) or empty($options))
      return;

    if(!isset($field_id) or empty($field_id) or !is_numeric($field_id))
      return;

    $db_option_ids = $this->get_option_ids($field_id);

    $option_ids = array();
    $options_by_id = array();
    $create_options = array();
    foreach($options as $option)
    {
      if(isset($option['id']) and !empty($option['id']) and is_numeric($option['id']))
      {
        $option_ids[] = $option['id'];
        $options_by_id[$option['id']] = $option;
      }
      else
        $create_options[] = $option;
    }

    // CREATE options
    foreach ($create_options as $option)
    {
      $this->create_option( $option['value'],
                            $option['label'],
                            $field_id );
    }

    // DELETE or UPDATE options
    foreach($db_option_ids as $db_id)
    {
      if(in_array($db_id, $option_ids))
      {
        $db_option = $options_by_id[$db_id];
        $this->update_option( $db_option['id'],
                              $db_option['value'],
                              $db_option['label'],
                              $db_option['field_id'] );
      }
      else
        $this->delete_option($db_id);
    }
  }

  function get_all_ids()
  {
    global $wpdb;
  
    $query = "SELECT id FROM {$this->table_name}";

    return $wpdb->get_col($query);
  }
  
  function get_all($type=OBJECT, $where='')
  {
    global $wpdb;
    
    if(!empty($where))
      $where = " WHERE {$where}";
  
    $query = "SELECT * FROM {$this->table_name}{$where}";

    return $wpdb->get_results($query, $type);
  }
  
  function get_field($field_id, $type=OBJECT)
  {
    global $wpdb;
  
    $query = "SELECT * FROM {$this->table_name} WHERE id=%d";
    $query = $wpdb->prepare( $query, $field_id );

    return $wpdb->get_row($query, $type);
  }
  
  function get_options($field_id, $type=OBJECT)
  {
    global $wpdb;
  
    $query = "SELECT * FROM {$this->options_table_name} WHERE field_id=%d";
    $query = $wpdb->prepare( $query, $field_id );

    return $wpdb->get_results($query, $type);
  }
  
  function get_option_ids($field_id)
  {
    global $wpdb;
  
    $query = "SELECT id FROM {$this->options_table_name} WHERE field_id=%d";
    $query = $wpdb->prepare( $query, $field_id );

    return $wpdb->get_col($query);
  }

  function get_value($user_id,$field_id)
  {
    global $wpdb;
    
    $query = "SELECT * FROM {$this->values_table_name} WHERE user_id=%d AND field_id=%d";
    $query = $wpdb->prepare($query, $user_id, $field_id);

    return $wpdb->get_row($query);
  }
  
  function create_or_update_value($user_id, $field_id, $value)
  {
    $db_value = $this->get_value($user_id, $field_id);
    
    if( $db_value and 
        isset($db_value->id) and 
        !empty($db_value->id) and 
        is_numeric($db_value->id) and
        (int)$db_value->id > 0 )
      return $this->update_value($db_value->id, $value);
    else
      return $this->create_value($value, $field_id, $user_id);
  }
}
?>