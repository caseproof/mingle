<?php
class MnglNotificationApi
{
  /** Before your plugin can send a mingle notification, it must register itself as a source by using the mngl-notification-types
    * filter to add a code (the key of your array entry), name and icon url. Here's an example from the MnglBoardsController class:
    *
    * function MnglBoardsController()
    * {
    *   add_filter('mngl-activity-types', array( $this, 'add_activity_types' ));
    * }
    * 
    * function add_activity_types($activity_types)
    * {
    *   $activity_types['board_posts'] = array( 'name' => 'Board Posts',
    *                                             'message' => '{$owner->screenname} commented on {$author_profile_link}\'s post';
    *                                             'icon' => MNGL_URL . '/images/board_post.png' );
    *   $activity_types['board_comments'] = array( 'name' => 'Board Comments',
    *                                                'message' => '{$owner->screenname} commented on {$author_profile_link}\'s post',
    *                                                'icon' => MNGL_URL . '/images/board_comment.png' );
    *   return $activity_types;
    * }
    *
    * Also, note that in the message variable above you can actually specify placeholders in normal php syntax for the
    * following variables:
    * 
    * $user_profile_url (the url of the owner's profile)
    * $user_profile_link (a full hyperlink to the author's profile, using his or her name as the anchor text)
    * 
    * $user->avatar (url to the avatar)
    * $user->full_name
    * $user->first_name
    * $user->last_name
    * $user->screenname
    * $user->email
    * $user->url (website url)
    * $user->bio
    * $user->sex
    * $user->location
    * $user->his_her
    * $user->him_her
    * $user->he_she

    */
  function send_notification( $user_login, $subject, $message, $message_type )
  {
    MnglNotification::send_notification_email_by_screenname($screenname, $subject, $message, $message_type);
  }
}
?>
