<?php

class MnglMessagesHelper {
  function format_party_list($parties)
  {
    global $mngl_user;
    
    $user_list = $mngl_user->get_profile_link(__('You', 'mingle'));

    foreach($parties as $index => $user_id)
    {
      if($user_id == $mngl_user->id)
        continue;
        
      $other_parties[] = $user_id;
    }
      
    $num_parties = count($other_parties);
    foreach($other_parties as $index => $user_id)
    {
      $user = MnglUser::get_stored_profile_by_id($user_id);
      if((int)$index < ((int)$num_parties - 1))
        $user_list .= __(', ', 'mingle');
      else
        $user_list .= __(' and ', 'mingle');
      
      $user_list .= $user->get_profile_link();
    }
    
    return sprintf(__("Between %s", 'mingle'), $user_list);
  }

  function get_pre_populate_tokens()
  {
    global $mngl_user, $mngl_friend;

    if(isset($_POST['mngl_message_recipients']) and !empty($_POST['mngl_message_recipients']))
    {
      $recipients = explode(',',preg_replace('#,\s*$#','',$_POST['mngl_message_recipients']));

      // Assume the message went through if we have a message body
      if(isset($_POST['mngl_message_body']) and !empty($_POST['mngl_message_body']))
        return '';
    }
    else if(isset($_GET['mu']) and !empty($_GET['mu']))
      $recipients = explode(',',$_GET['mu']);
    else
      return '';
    
    $where_clause = "status='verified' AND friend_id" . ((count($recipients) > 1)?' IN ('.implode(',',$recipients).')':'='.$recipients[0]);

    $friends_array = $mngl_friend->get_all_by_user_id( $mngl_user->id, $where_clause );
    $fmt_friends_array = array();
    foreach($friends_array as $friend)
    {
      $avatar = get_avatar($friend->ID, 20);
      $screenname = $friend->user_login;
      
      $fmt_friends_array[] = array("id" => $friend->ID, "name" => "<span class='mngl_friend_dropdown_item'>{$avatar}&nbsp;<span class='mngl_friend_dropdown_text'>{$screenname}</span></span>");
    }
      
    return ",prePopulate:" . MnglAppHelper::json_encode($fmt_friends_array);
  }
}

?>
