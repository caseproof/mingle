<?php

class MnglBoardsHelper
{
  function &get_stored_object()
  { 
    static $this_object;

    if( !isset($this_object) or 
        empty($this_object) or
        !is_object(&$this_object) )
      $this_object =& new MnglBoardsHelper();
    
    return $this_object;
  }

  function display_message($message_class, $message, $autoembed_media=true)
  {
    $hidden = '';

    $formatted_message = MnglBoardsHelper::format_message($message, false, $autoembed_media);

    if(strlen($message) >= 255)
    {
      $hidden = ' class="mngl-hidden"';
      $teaser = MnglBoardsHelper::format_message((mb_substr($message,0,255) . "..."), false, false);
      $teaser = MnglBoardsHelper::strip_bbcodes($teaser); // get rid of any unmatched bbcodes
      $class_suffix = '-fake';
      
      $teaser_class = $message_class . $class_suffix;
      
      ?><span id="<?php echo $teaser_class; ?>"><?php echo $teaser; ?> <a href="javascript:mngl_toggle_two_ids('#<?php echo $message_class; ?>','#<?php echo $teaser_class; ?>')"><?php _e('Read More', 'mingle'); ?></a></span><?php
    }
    ?><span id="<?php echo $message_class; ?>"<?php echo $hidden; ?>><?php echo $formatted_message; ?></span><?php
  }
  
  function format_message($message, $strip_newlines=false, $autoembed_media=true)
  {
    global $wp_smiliessearch, $wpsmiliestrans;

    $message = stripslashes($message);

    //$message = MnglBoardsHelper::parse_textile($message);
    $message = MnglBoardsHelper::parse_bbcodes($message);

    if($strip_newlines)
      $message = preg_replace("#\n#"," ",$message);
    else
      $message = preg_replace("#\n#","<br/>",$message);
    
    $message = wptexturize($message);
    
    /* Okay -- so this works -- but I've gotta think through if this is how I want embedding to happen
    if($autoembed_media)
      $message = MnglBoardsHelper::autoembed_media($message);
    */
    
    $message = convert_smilies($message);
    $message = make_clickable($message);
    $message = MnglBoardsHelper::make_tags_clickable($message);
    
    $message = apply_filters('mngl-format-message',$message);

    return $message;
  }

  function style_text($message)
  {
    $message = preg_replace('#\*([^\*\s].*?)\*#','<b>$1</b>',$message); 
    $message = preg_replace('#-([^-\s].+?)-#','<s>$1</s>',$message); 
    $message = preg_replace('#_([^_\s].+?)_#','<em>$1</em>',$message); 
    return $message;
  }

  function parse_textile($content, $safe_tags = '<a>, <b>, <i>, <u>, <blockquote>, <code>')
  {
    $content = ' ' . trim($content) . ' ';
    $modifiers = Array('\*\*'=>'b', '\__'=>'i', '\*'=>'strong', '\_'=>'em', '\-'=>'del', '\?\?'=>'cite', '%'=>'span', '\+'=>'ins', '\^'=>'sup', '\~'=>'sub', '@'=>'code');
    $content = strip_tags($content, $safe_tags);
    $content = ereg_replace('([[:space:]])"[[:<:]](.*)[[:>:]]"([[:space:]])', '\1&#8220;\2&#8221;\3', $content);
    $content = ereg_replace('([)([[:alnum:]]){1,5}(])', '<sup><a href="#fn\1">\1</a></sup>', $content);
    $content = ereg_replace('(<p>)(fn([[:alnum:]]){1,}. )([[:alnum:][:punct:][:space:]]+)(<\/p>)', '<p id="fn\3">\4</p>', $content);
    $content = str_replace(Array('--', '...', ' x ', '(TM)', '(R)', '(C)'), Array('&#8212;', '&#8230;', ' &#215; ', '&#8482;', '&#174;', '&#169;'), $content);

    while (list($key, $value) = @each($modifiers)) {
        $content = ereg_replace('([[:space:]])' . $key . '([[:alnum:]\(\)\*_-~\\+\\^!?\.<\\/>]{1,}([[:alnum:][:space:]\(\)\*_-~\\+\\^!?\.<\\/>"]+)?)' . $key, '\1<' . $value . '>\2</' . $value . '>', $content);
    }
     
    $content = ereg_replace('([[:alnum:]]+)(\()+([[:alnum:][:space:]]+)(\))', '<acronym title="\3">\1</acronym>', $content);
    $content = ereg_replace('([[:space:]])((http://)[[:alnum:][:punct:]]+)([[:space:]])?', '\1<a href="\2" rel="nofollow">\2</a>\4', $content);
    return $content;
  }

  function strip_bbcodes($content)
  {
    // The array of regex patterns to look for
    $format_search =  array(
      '#\[/?b\]\n?#is',
      '#\[/?i\]\n?#is',
      '#\[/?u\]\n?#is',
      '#\[/?s\]\n?#is',
      '#\[/?code\]\n?#is',
      '#\[/?color(=.*?)?\]\n?#is',
      '#\[/?url(=.*?)?\]\n?#i',
      '#\[/?list(=.*?)?\]\n?#i',
      '#\[\*\]#i'
    );
    // The matching array of strings to replace matches with
    $format_replace = array(
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '',
      '*'
    );

    return preg_replace($format_search,$format_replace,$content);
  }

  function parse_bbcodes($content)
  {
    // The array of regex patterns to look for
    $format_search =  array(
      '#\[b\](.*?)\[/b\]#is', // Bold ([b]text[/b]
      '#\[i\](.*?)\[/i\]#is', // Italics ([i]text[/i]
      '#\[u\](.*?)\[/u\]#is', // Underline ([u]text[/u])
      '#\[s\](.*?)\[/s\]#is', // Strikethrough ([s]text[/s])
      '#\[code\](.*?)\[/code\]#is', // Monospaced code [code]text[/code])
      '#\[color=\#?([A-F0-9]{3}|[A-F0-9]{6})\](.*?)\[/color\]#is', // Font color ([color=#00F]text[/color])
      '#\[url=((?:ftp|https?)://.*?)\](.*?)\[/url\]#i', // Hyperlink with descriptive text ([url=http://url]text[/url])
      '#\[url\]((?:ftp|https?)://.*?)\[/url\]#i'//, // Hyperlink ([url]http://url[/url])
      //'#\[size=([1-9]|1[0-9]|20)\](.*?)\[/size\]#is', // Font size 1-20px [size=20]text[/size])
      //'#\[quote\](.*?)\[/quote\]#is', // Quote ([quote]text[/quote])
      //'#\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]#i' // Image ([img]http://url_to_image[/img])
    );
    // The matching array of strings to replace matches with
    $format_replace = array(
      '<strong>$1</strong>',
      '<em>$1</em>',
      '<span style="text-decoration: underline;">$1</span>',
      '<span style="text-decoration: line-through;">$1</span>',
      "<div class=\"mngl-code\">\$1</div>",
      '<span style="color: #$1;">$2</span>',
      '<a href="$1" rel="nofollow">$2</a>',
      '$1'//, make_clickable will handle this
      //'<span style="font-size: $1px;">$2</span>',
      //'<blockquote>$1</blockquote>',
      //'<img src="$1" alt="" />'
    );

    $content = preg_replace($format_search,$format_replace,$content);

    $mngl_boards_helper =& MnglBoardsHelper::get_stored_object();
    return preg_replace_callback( '#\[list(=.)?\]\n?(.*?)\[/list\]\n?#is', array(&$mngl_boards_helper, 'format_bbcode_list'), $content );
  }

  function format_bbcode_list($matches)
  {
    if(preg_match('#\=[a-z]#i', $matches[1]))
    {
      $tag = "ol";
      $style = " style=\"list-style-type: lower-alpha;\"";
    }
    else if(preg_match('#\=\d#', $matches[1]))
    {
      $tag = "ol";
      $style = " style=\"list-style-type: decimal;\"";
    }
    else
    {
      $tag = "ul";
      $style = "";
    }

    $list_tok = '[*]';
    $list_item = strtok( trim($matches[2]), $list_tok );
    $list_item_str = "";

    while( $list_item !== false)
    {
      $curr_item = trim($list_item);
      if(!empty($curr_item))
        $list_item_str .= "<li>{$curr_item}</li>";

      $list_item = strtok( $list_tok );
    }

    return "<{$tag}{$style}>{$list_item_str}</{$tag}>";
  }

  function escape_code_blocks($content)
  {
    $code_search = '#(\[code\]\n?)(.*?)(\[/code\]\n?)#is'; // Monospaced code [code]text[/code]
    
    $mngl_boards_helper =& MnglBoardsHelper::get_stored_object();
    return preg_replace_callback( $code_search, array(&$mngl_boards_helper, 'escape_code_block_callback'), $content );
  }

  function escape_code_block_callback($matches)
  {
    $format_search =  array(
      '#\<#',
      '#\>#',
      '#  #', // assume indentation will consist of 2x spaces next to each other
      '#\t#'  // or tabs, of course
    );

    $format_replace = array(
      '&lt;',
      '&gt;',
      '&nbsp;&nbsp;',
      '&nbsp;&nbsp;'
    );

    return trim($matches[1]) . preg_replace( $format_search, $format_replace, $matches[2] ) . trim($matches[3]);
  }

  function autoembed_media($message)
  {
    $mngl_boards_helper =& MnglBoardsHelper::get_stored_object();
    return preg_replace_callback( '#(https?://[^\s"]+)#im', array(&$mngl_boards_helper, 'autoembed_media_callback'), $message, 1 );
  }
  
  function autoembed_media_callback($match)
  {
    global $wp_embed;
    $return = $wp_embed->shortcode( array( 'width' => 350 ), $match[1] );
    return "\n$return\n";
  }

  function board_post_url($board_post_id)
  {
    global $mngl_options;

    if(isset($mngl_options->profile_page_id) and $mngl_options->profile_page_id != 0)
    {
      $permalink = get_permalink($mngl_options->profile_page_id);
      $param_char = ((preg_match("#\?#",$permalink))?'&':'?');
      return "{$permalink}{$param_char}mbpost={$board_post_id}";
    }

    return '';
  }
  
  function &get_tagged_users($message)
  {
    global $mngl_options;
    preg_match_all('#@([a-zA-Z0-9_\-\.]+)#', $message, $matches);
    
    $users = array();
    $usernames = array();

    if(is_array($matches[1]))
    {
      foreach($matches[1] as $index => $username)
      {  
        require_once(ABSPATH . WPINC . '/registration.php');

        if( !in_array($username,$usernames) and
            $user_id = username_exists($username) and
            !in_array($user_id,$mngl_options->invisible_users) )
        {
          $usernames[] = $username;
          $users[] =& MnglUser::get_stored_profile_by_screenname($username);
        }
      }
    }

    return $users;
  }
  
  function make_tags_clickable($message)
  {
    $users =& MnglBoardsHelper::get_tagged_users($message);
  
    if($users and is_array($users))
    {
      foreach($users as $user)
      {
        $preg_string = "#@({$user->screenname})#";
        $preg_link   = '@<a href="' . $user->get_profile_url() . '">$1</a>';
        $message     = preg_replace( $preg_string, $preg_link, $message );
      }
    }
    return $message;
  }
}
?>
