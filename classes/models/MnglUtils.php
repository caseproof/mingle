<?php

class MnglUtils
{
  function get_user_id_by_email($email)
  {
    if(isset($email) and !empty($email))
    {
      global $wpdb;
      $query = "SELECT ID FROM {$wpdb->users} WHERE user_email=%s";
      $query = $wpdb->prepare($query, mysql_escape_string($email));
      return (int)$wpdb->get_var($query);
    }
    
    return '';
  }
  
  function is_image($filename)
  {
    if(!file_exists($filename))
      return false;

    $file_meta = getimagesize($filename);
    
    $image_mimes = array("image/gif", "image/jpeg", "image/png");
    
    return in_array($file_meta['mime'], $image_mimes);
  }
  
  function rewriting_on()
  {
    $permalink_structure = get_option('permalink_structure');
    
    return ($permalink_structure and !empty($permalink_structure));
  }
  
  // Returns a list of just user data from the wp_users table
  function get_raw_users($where = '', $order_by = 'user_login')
  {
    global $wpdb;

    static $raw_users;
    
    if(!isset($raw_users))
    {
      $where    = ((empty($where))?'':" WHERE {$where}");
      $order_by = ((empty($order_by))?'':" ORDER BY {$order_by}");
      
      $query = "SELECT * FROM {$wpdb->users}{$where}{$order_by}";
      $raw_users = $wpdb->get_results($query);
    }
    
    return $raw_users;
  }

  /* We issue this check because we may want to use the username as a slug at some point */
  function username_is_available( $username )
  {
    global $wpdb, $mngl_blogurl;
  
    // Check username uniqueness against posts, pages and categories
    $query     = "SELECT post_name FROM {$wpdb->posts} WHERE post_name=%s";
    $query     = $wpdb->prepare($query,$username);
    $post_slug = $wpdb->get_var($query);
    
    $query     = "SELECT slug FROM {$wpdb->terms} WHERE slug=%s";
    $query     = $wpdb->prepare($query,$username);
    $term_slug = $wpdb->get_col($query);
  
    if( $post_slug == $username or $term_slug == $username )
      return false;
  
    // Check slug against files on the root wordpress install
    $root_dir = opendir(ABSPATH);
  
    while (($file = readdir($root_dir)) !== false)
    {
      $haystack = strtolower($file);
      if($haystack == $slug)
        return false;
    }
  
    // Check slug against other slugs in the prli links database.
    // We'll use the full_slug here because its easier to guarantee uniqueness.
    if(!function_exists('is_plugin_active'))
      require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

    if(is_plugin_active('pretty-link/pretty-link.php'))
    {
      global $prli_utils;
      return $prli_utils->slugIsAvailable($username);
    }
  
    return true;
  }
 
  function get_permalink_pre_slug_uri($force=false,$trim=false)
  {
    preg_match('#^([^%]*?)%#', get_option('permalink_structure'), $struct);
    $pre_slug_uri = $struct[1];

    if($force or preg_match('#index\.php#', $pre_slug_uri))
    {
      if($trim)
      {
        $pre_slug_uri = trim($pre_slug_uri);
        $pre_slug_uri = preg_replace('#^/#','',$pre_slug_uri);
        $pre_slug_uri = preg_replace('#/$#','',$pre_slug_uri);
      }

      return $pre_slug_uri;
    }
    else
      return '/';
  }
  
  function &php_get_browsercap_ini()
  {
    // Since it's a fairly expensive proposition to load the ini file
    // let's make sure we only do it once
    static $browsecap_ini;
    
    if(!isset($browsecap_ini))
    {
      if( version_compare(PHP_VERSION, '5.3.0') >= 0 )
        $browsecap_ini =& parse_ini_file( MNGL_PATH . "/includes/php/php_browsecap.ini", true, INI_SCANNER_RAW );
      else
        $browsecap_ini =& parse_ini_file( MNGL_PATH . "/includes/php/php_browsecap.ini", true );
    }
    
    return $browsecap_ini;
  }
  
  /* Needed because we don't know if the target uesr will have a browsercap file installed
     on their server ... particularly in a shared hosting environment this is difficult
  */
  function php_get_browser($agent = NULL)
  {
    $agent=$agent?$agent:$_SERVER['HTTP_USER_AGENT'];
    $yu=array();
    $q_s=array("#\.#","#\*#","#\?#");
    $q_r=array("\.",".*",".?");
    $brows =& MnglUtils::php_get_browsercap_ini();

    if(!empty($brows) and $brows and is_array($brows))
    {
      foreach($brows as $k=>$t)
      {
        if(fnmatch($k,$agent))
        {
          $yu['browser_name_pattern']=$k;
          $pat=preg_replace($q_s,$q_r,$k);
          $yu['browser_name_regex']=strtolower("^$pat$");
          foreach($brows as $g=>$r)
          {
            if($t['Parent']==$g)
            {
              foreach($brows as $a=>$b)
              {
                if($r['Parent']==$a)
                {
                  $yu=array_merge($yu,$b,$r,$t);
                  foreach($yu as $d=>$z)
                  {
                    $l=strtolower($d);
                    $hu[$l]=$z;
                  }
                }
              }
            }
          }
      
          break;
        }
      }
    }
  
    return $hu;
  }
  
  function is_robot()
  {
    $ua_string = trim(urldecode($_SERVER['HTTP_USER_AGENT']));

    // Yah, if the whole user agent string is missing -- wtf?
    if(empty($ua_string))
      return 1;

    // Some bots actually say they're bots right up front let's get rid of them asap
    if(preg_match("#(bot|spider|crawl)#i",$ua_string))
      return 1;
      
    $browsecap = MnglUtils::php_get_browser($ua_string);
    $btype = trim($browsecap['browser']);

    $crawler = $browsecap['crawler'];

    // If php_browsecap tells us its a bot, let's believe it
    if($crawler == 1)
      return 1;

    // If the Browser type was unidentifiable then it's most likely a bot
    if(empty($btype))
      return 1;

    return 0;
  }
  
  /***** Captcha Utility Functions *****/

  function str_encrypt($str)
  { 
    $mystr = MnglUtils::RC4($str, MnglUtils::wp_salt());
    $mystr = rawurlencode(base64_encode($mystr));
    return $mystr;
  }

  function str_decrypt($str) 
  {
    $mystr = base64_decode(rawurldecode($str));  
    $mystr = MnglUtils::RC4($mystr, MnglUtils::wp_salt());
    return $mystr;
  }

  // ------------------------------------------------------------------------------
  // Function    : RC4($data, $key)
  // Description : ecncrypt/decrypt $data with the key in $keyfile with an rc4 algorithm 
  //               This was written by danzarrella in 2002 can be found on Zend.com
  // Return      : string (encrypted/decrypted)
  // ------------------------------------------------------------------------------
  function RC4($data, $key)
  {
    // initialize (modified by Simon Lee)
    $x=0; $j=0; $a=0; $temp=""; $Zcrypt=""; 
    for ($i=0; $i<=255; $i++) {
      $counter[$i] = "";
    }

      // $pwd = implode('', file($keyfile)); 
      $pwd = $key;
          $pwd_length = strlen($pwd); 
      for ($i = 0; $i < 255; $i++) { 
            $key[$i] = ord(substr($pwd, ($i % $pwd_length)+1, 1)); 
              $counter[$i] = $i; 
          } 
          for ($i = 0; $i < 255; $i++) { 
              $x = ($x + $counter[$i] + $key[$i]) % 256; 
              $temp_swap = $counter[$i]; 
              $counter[$i] = $counter[$x]; 
              $counter[$x] = $temp_swap; 

          } 
          for ($i = 0; $i < strlen($data); $i++) { 
                          $a = ($a + 1) % 256; 
              $j = ($j + $counter[$a]) % 256; 
              $temp = $counter[$a]; 
              $counter[$a] = $counter[$j]; 
              $counter[$j] = $temp; 
              $k = $counter[(($counter[$a] + $counter[$j]) % 256)]; 
              $Zcipher = ord(substr($data, $i, 1)) ^ $k; 
              $Zcrypt .= chr($Zcipher); 
          } 
          return $Zcrypt; 
  }
  
  function generate_random_code($characters)
  {
    /* list all possible characters, similar looking characters and vowels have been removed */
    $possible = '23456789bcdfghjkmnpqrstvwxyz';
    $code = '';
    $i = 0;
    while ($i < $characters) 
    { 
      $code .= substr($possible, mt_rand(0, strlen($possible)-1), 1);
      $i++;
    }
    return $code;
  }
  
  function is_version_at_least( $version )
  {
    global $wp_version;
    
    return version_compare( $wp_version, $version, '>=' );
  }
  
/* PLUGGABLE FUNCTIONS AS TO NOT STEP ON OTHER PLUGINS' CODE */
  function get_currentuserinfo()
  {
    MnglUtils::_include_pluggables('get_currentuserinfo');
    return get_currentuserinfo();
  }

  function &get_userdata($id)
  {
    MnglUtils::_include_pluggables('get_userdata');
    return get_userdata($id);
  }

  function get_userdatabylogin($screenname)
  {
    MnglUtils::_include_pluggables('get_userdatabylogin');
    return get_userdatabylogin($screenname);
  }

  function wp_mail($recipient, $subject, $message, $header)
  {
    MnglUtils::_include_pluggables('wp_mail');
    return wp_mail($recipient, $subject, $message, $header);
  }

  function is_user_logged_in()
  {
    MnglUtils::_include_pluggables('is_user_logged_in');
    return is_user_logged_in();
  }

  function get_avatar( $id, $size )
  {
    MnglUtils::_include_pluggables('get_avatar');
    return get_avatar( $id, $size );
  }
  
  function wp_hash_password( $password_str )
  {
    MnglUtils::_include_pluggables('wp_hash_password');
    return wp_hash_password( $password_str );
  }
  
  function wp_generate_password( $length, $special_chars )
  {
    MnglUtils::_include_pluggables('wp_generate_password');
    return wp_generate_password( $length, $special_chars );
  }
  
  function wp_redirect( $location, $status=302 )
  {
    MnglUtils::_include_pluggables('wp_redirect');
    return wp_redirect( $location, $status );
  }
  
  function wp_salt( $scheme='auth' )
  {
    MnglUtils::_include_pluggables('wp_salt');
    return wp_salt( $scheme );
  }

  function _include_pluggables($function_name)
  {
    if(!function_exists($function_name))
      require_once(ABSPATH . WPINC . '/pluggable.php');
  }
}
?>
