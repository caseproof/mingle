<?php

class MnglAppHelper
{
  function MnglAppHelper()
  {
  }

  function get_avatar_img($user, $size, $filtered_img = false)
  {
    if($user)
    {
      $square = ((int)$size < 150); // if greater than 150 then this isn't a square
      $avatar_url = $user->get_sized_avatar_url($size,$square);

      if(!isset($avatar_url) or !$avatar_url or empty($avatar_url))
        return $filtered_img;

      if(!$square or !$filtered_img)
      {
        $avatar_dim = $user->get_avatar_dimensions($size,$square);

        if($avatar_dim['width'] > 0 or $avatar_dim['height'] > 0)
          $image_style = ' style="' . (($avatar_dim['width'] > 0)?"width: " . $avatar_dim['width'] . "px;":'') . (($avatar_dim['height'] > 0)?"height: " . $avatar_dim['height'] . "px;":'') . '"';
        else
          $image_style = '';

        $avatar_img = "<img src=\"{$avatar_url}\" class=\"{$classes}\"{$image_style}/>";
      }
      else
        $avatar_img = preg_replace('#(src=["\']).*?(["\'])#',"$1{$avatar_url}$2",$filtered_img);

      return $avatar_img;
    }
    else
      return filtered_img;
  }
  
  function link_avatar($user_id,$avatar_img)
  {
    $user = MnglUser::get_stored_profile_by_id($user_id, false);

    if($user)
      return "<a href=\"" . $user->get_profile_url() . "\">{$avatar_img}</a>";
    else
      return $avatar_img;
  }
  
  function add_avatar_class($img, $class)
  {
    return preg_replace('#(class=["\'])#', "$1{$class} ",$img);
  }
  
  function get_avatar_img_by_id($user_id, $size, $filtered_img=false)
  {
    $user = MnglUser::get_stored_profile_by_id($user_id, false);

    if($user)
      return MnglAppHelper::get_avatar_img($user, $size, $filtered_img);
    else
      return $filtered_img;
  }
  
  function get_avatar_img_by_screenname($screenname, $size, $filtered_img=false)
  {
    $user = MnglUser::get_stored_profile_by_screenname($screenname, false);

    if($user)
      return MnglAppHelper::get_avatar_img($user, $size, $filtered_img);
    else
      return $filtered_img;
  }

  function get_pages()
  {
    global $wpdb;
    
    $query = "SELECT * FROM {$wpdb->posts} WHERE post_status=%s AND post_type=%s";
    
    $query = $wpdb->prepare( $query, "publish", "page" );
    
    $results = $wpdb->get_results( $query );
    
    if($results)
      return $results;
    else
      return array();
  }
  
  // Displays time as in how many seconds / minutes / hours / days / weeks ago
  function time_ago($created_at_ts)
  {
    if(!isset($created_at_ts) or !$created_at_ts)
     return '';

    $ago = time() - $created_at_ts;
    $ago_str = '';
    
    $minutes = 60;
    $hours   = $minutes * 60;
    $days    = $hours * 24;
    $weeks   = $days * 7;
    $months  = $days * 30;
    $years   = $days * 365;
    
    if($ago < $minutes)
    {
      $ago_str = sprintf(__ngettext("%d second ago","%d seconds ago", $ago, 'mingle'), $ago);
    }
    else if($ago < $hours)
    {
      $ago_minutes = (int)($ago / $minutes);
      
      $ago_str = sprintf(__ngettext("%d minute ago", "%d minutes ago", $ago_minutes, 'mingle'), $ago_minutes);
    }
    else if($ago < $days)
    {
      $ago_hours = (int)($ago / $hours);
      
      $ago_str = sprintf(__ngettext("%d hour ago", "%d hours ago", $ago_hours, 'mingle'), $ago_hours);
    }
    else if($ago < $weeks)
    {
      $ago_days = (int)($ago / $days);
      
      $ago_str = sprintf(__ngettext("yesterday", "%d days ago", $ago_days, 'mingle'), $ago_days);
    }
    else if($ago < $months)
    {
      $ago_weeks = (int)($ago / $weeks);
      
      $ago_str = sprintf(__ngettext("last week", "%d weeks ago", $ago_weeks, 'mingle'), $ago_weeks);
    }
    else if($ago < $years)
    {
      $ago_months = (int)($ago / $months);
      
      $ago_str = sprintf(__ngettext("last month", "%d months ago", $ago_months, 'mingle'), $ago_months);
    }
    else
    {
      $ago_years = (int)($ago / $years);
      
      $ago_str = sprintf(__ngettext("last year", "%d years ago", $ago_years, 'mingle'), $ago_years);
    }
    
    return $ago_str;
  }
  
  function value_is_selected($field_name, $field_value, $selected_value)
  {
    if( (isset($_POST[$field_name]) and $_POST[$field_name] == $selected_value) or
        (!isset($_POST[$field_name]) and $field_value == $selected_value) )
      echo ' selected="selected"';
  }
  
  function value_is_checked($field_name, $field_value)
  {
    if( (isset($_POST) and $_POST[$field_name] == 'on') or
        (!isset($_POST) and $field_value == 'on') )
      echo ' checked="checked"';
  }

  function value_is_checked_with_array($field_name, $index, $field_value)
  {
    if( ( $_POST['action'] == 'process_form' and isset( $_POST[ $field_name ][ $index ] ) ) or
        ( $_POST['action'] != 'process_form' and isset($field_value) ) )
      echo ' checked="checked"';
  }
  
  function powered_by()
  {
    $show_powered_by = apply_filters('mngl-show-powered-by',true);
    
    if($show_powered_by)
    {
    ?>
      <p style="font-size: 8px;"><?php printf(__('Powered by %1$sMingle%2$s', 'mingle'), '<span><img src="' . MNGL_URL . '/images/mingle_16.png" alt="Small Mingle Icon" width="10px" height="10px">&nbsp;<a href="http://blairwilliams.com/mingle" style="text-decoration: none;">', '</a></span>'); ?></p>
    <?php
    }
  }
  
  function get_extension( $mimetype )
  {
    switch( $mimetype )
    {
      case "application/msword":
      case "application/rtf":
      case "text/richtext":
        return "doc";
      case "application/vnd.ms-excel":
        return "xls";
      case "application/vnd.ms-powerpoint":
        return "ppt";
      case "application/pdf":
        return "pdf";
      case "application/zip":
        return "zip";
      case "image/jpeg":
        return "jpg";
      case "image/gif":
        return "gif";
      case "image/png":
        return "png";
      case "image/tiff":
        return "tif";
      case "text/plain":
        return "txt";
      case "text/html":
        return "html";
      case "video/quicktime":
        return "mov";
      case "video/x-msvideo":
        return "avi";
      case "video/x-ms-wmv":
        return "wmv";
      case "video/ms-wmv":
        return "wmv";
      case "video/mpeg":
        return "mpg";
      case "audio/mpg":
        return "mp3";
      case "audio/x-m4a":
        return "aac";
      case "audio/m4a":
        return "aac";
      case "audio/x-wav":
        return "wav";
      case "audio/wav":
        return "wav";
      case "application/x-zip-compressed":
        return "zip";
      default:
        return "bin";
    }
  }
  
  
  function decode_unicode($val)
  { 
    $val = preg_replace_callback("/%u([0-9a-fA-F]{4})/",
                                 create_function(
                                   '$matches',
                                   'return html_entity_decode("&#".hexdec($matches[1]).";",ENT_COMPAT,"UTF-8");'
                                 ),
                                 $val);
    return $val;
  }
  
  // Detects whether an array is a true numerical array or an
  // associative array (or hash).
  function array_type($item)
  {
    $array_type = 'unknown';

    if(is_array($item))
    {
      $array_type = 'array';

      foreach($item as $key => $value)
      {
        if(!is_numeric($key))
        {
          $array_type = 'hash';
          break;
        }
      }
    }

    return $array_type;
  }

  // This eliminates the need to use php's built in json_encoder
  // which only works with PHP 5.2 and above.
  function json_encode($json_array)
  {
    $json_str = '';

    if(is_array($json_array))
    {
      if(self::array_type($json_array) == 'array')
      {
        $first = true;
        $json_str .= "[";
        foreach($json_array as $item)
        {
          if(!$first)
            $json_str .= ",";

          if(is_numeric($item))
            $json_str .= (($item < 0)?"\"$item\"":$item);
          else if(is_array($item))
            $json_str .= self::json_encode($item);
          else if(is_string($item))
            $json_str .= '"'.$item.'"';
          else if(is_bool($item))
            $json_str .= (($item)?"true":"false");

          $first = false;
        }
        $json_str .= "]";
      }
      else if(self::array_type($json_array) == 'hash')
      {
        $first = true;
        $json_str .= "{";
        foreach($json_array as $key => $item)
        {
          if(!$first)
            $json_str .= ",";

          $json_str .= "\"$key\":";

          if(is_numeric($item))
            $json_str .= (($item < 0)?"\"$item\"":$item);
          else if(is_array($item))
            $json_str .= self::json_encode($item);
          else if(is_string($item))
            $json_str .= "\"$item\"";
          else if(is_bool($item))
            $json_str .= (($item)?"true":"false");

          $first = false;
        }
        $json_str .= "}";
      }
    }

    return $json_str;
  }

  // This eliminates the need to use php's built in json_encoder
  // which only works with PHP 5.2 and above.
  function json_decode(&$json_str,$type='array',$index = 0)
  {
    $json_array = array();
    $index_str = '';
    $value_str = '';
    $in_string = false;
    $in_index = ($type=='hash'); //first char in hash is an index
    $in_value = ($type=='array'); //first char in array is a value

    $json_special_chars_array = array('{','[','}',']','"',',',':');

    // On the first pass we need to do some special stuff
    if($index == 0)
    {
      if($json_str[$index] == '{')
      {
        $type = 'hash';
        $in_index = true;
        $in_value = false;
      }
      else if($json_str[$index]=='[')
      {
        $type = 'array';
        $in_index = false;
        $in_value = true;
      }
      else
        return false; // not valid json

      // skip to next index
      $index++;
    }

    for($i = $index; $i < strlen($json_str); $i++)
    {
      if($in_string and in_array($json_str[$i],$json_special_chars_array))
      {
        if($json_str[$i] == '"')
          $in_string = false;
        else
        {
          if($in_value)
            $value_str .= $json_str[$i];
          else if($in_index)
            $index_str .= $json_str[$i];
        }
      }
      else
      {
        switch($json_str[$i])
        {
          case '{':
            $array_vals = self::json_decode($json_str,'hash',$i + 1);

            if($type=='hash')
              $json_array[$index_str] = $array_vals[1]; // We'll never get an array as an index
            else if($type=='array')
              $json_array[] = $array_vals[1];

            $i = $array_vals[0]; // Skip ahead to the new index
            break;

          case '[':
            $array_vals = self::json_decode($json_str,'array',$i + 1);

            if($type=='hash')
              $json_array[$index_str] = $array_vals[1];
            else if($type=='array')
              $json_array[] = $array_vals[1];

            $i = $array_vals[0]; // Skip ahead to the new index
            break;

          case '}':
            if(!empty($index_str) and !empty($value_str))
            {
              $json_array[$index_str] = self::decode_json_unicode($value_str);
              $index_str = '';
              $value_str = '';
            }
            return array($i,$json_array);

          case ']':
            if(!empty($value_str))
            {
              $json_array[] = self::decode_json_unicode($value_str);
              $value_str = '';
            }
            return array($i,$json_array);

          // skip the null character
          case '\0':
              break;

          // Handle Escapes
          case '\\':
            if($in_string)
            {
              if(in_array($json_str[$i + 1],$json_special_chars_array))
              {
                if($in_value)
                  $value_str .= '\\'.$json_str[$i + 1];
                else if($in_index)
                  $index_str .= '\\'.$json_str[$i + 1];

                $i++; // skip the escaped char now that its been recorded
              }
              else
              {
                if($in_value)
                  $value_str .= $json_str[$i];
                else if($in_index)
                  $index_str .= $json_str[$i];
              }
            }
            break;

          case '"':
            $in_string = !$in_string; // just tells us if we're in a string
            break;

          case ':':
            if($type == 'hash')
            {
              $in_value = true;
              $in_index = false;
            }
            break;

          case ',':
            if($type == 'hash')
            {
              if(!empty($index_str) and !empty($value_str))
              {
                $json_array[$index_str] = self::decode_json_unicode($value_str);
                $index_str = '';
                $value_str = '';
              }

              $in_index = true;
              $in_value = false;
            }
            else if($type == 'array')
            {
              if(!empty($value_str))
              {
                $json_array[] = self::decode_json_unicode($value_str);
                $value_str = '';
              }

              $in_value = true;
              $in_index = false; // always false in an array
            }
            break;

          // record index and value
          default:
            if($in_value)
              $value_str .= $json_str[$i];
            else if($in_index)
              $index_str .= $json_str[$i];
        }
      }
    }

    return array(-1,$json_array);
  }

  function decode_json_unicode($val)
  { 
    $val = preg_replace_callback("/\\\u([0-9a-fA-F]{4})/",
                                 create_function(
                                   '$matches',
                                   'return html_entity_decode("&#".hexdec($matches[1]).";",ENT_COMPAT,"UTF-8");'
                                 ),
                                 $val);
    return $val;
  }
  
  function format_text($message)
  { 
    $message = stripslashes($message);
    
    return $message;
  }
}

?>
