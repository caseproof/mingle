function mngl_request_friend( mingle_url, user_id, friend_id, friend_requested_text )
{
  mngl_replace_id_with_loading_indicator('friend_request_button-' + friend_id);
  jQuery.ajax( {
    type: "POST",
    url: mingle_url,
    data: "controller=friends&action=friend_request&user_id=" + user_id + "&friend_id=" + friend_id,
    success: function(html) {
      jQuery("#friend_request_button-" + friend_id ).replaceWith( friend_requested_text );
    }
  });
}

function mngl_escape(message)
{
  // escape problematic characters -- don't escape utf8 chars
  return message.replace(/&/g,'%26').replace(/=/g,'%3D').replace(/ /g, '%20').replace(/\?/g, '%3F');
}

function mngl_post_to_board( mingle_url, owner_id, author_id, message, controller )
{  
  var mnglparams = jQuery('#mngl-board-post-button').attr('mnglparams');
  mngl_replace_id_with_loading_indicator('mngl-board-post-button');
  
  if(mnglparams==undefined)
  {
    mnglparams = '';
  }

  jQuery.ajax( {
    type: "POST",
    url: mingle_url,
    data: "controller=" + controller + "&action=post&owner_id=" + owner_id + "&author_id=" + author_id + "&message=" + mngl_escape(message) + mnglparams,
    success: function(html) {
      jQuery('.mngl-board').replaceWith('<div class="mngl-board">'+html+'</div>');
      mngl_load_growables();
    }
  });
}

function mngl_clear_status(user_id)
{
  mngl_replace_id_with_loading_indicator('mngl-clear-status-button');
  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "controller=boards&action=clear_status&mu=" + user_id,
    success: function(html) {
      jQuery('.mngl-profile-status').slideUp();
    }
  });
}

function mngl_show_older_posts( pagenum, loc, screenname )
{
  mngl_replace_id_with_loading_indicator('mngl-older-posts');
  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "controller=boards&action=older_posts&mdp=" + pagenum + "&loc=" + loc + "&mu=" + screenname,
    success: function(html) {
      jQuery('#mngl-older-posts').replaceWith(html);
      mngl_load_growables();
    }
  });
}

function mngl_comment_on_post( mingle_url, author_id, board_post_id, message, controller )
{
  mngl_replace_id_with_loading_indicator('mngl-comment-button-' + board_post_id);
  jQuery.ajax( {
    type: "POST",
    url: mingle_url,
    data: "controller=" + controller + "&action=comment&author_id=" + author_id + "&board_post_id=" + board_post_id + "&message=" + mngl_escape(message),
    success: function(html) {
      jQuery('#mngl-comment-form-wrap-'+board_post_id).replaceWith(html);
      mngl_load_growables();
      jQuery("#mngl-board-comment-list-" + board_post_id).show();
      jQuery("#mngl-fake-board-comment-" + board_post_id).show();
    }
  });
}

function mngl_delete_board_post( mingle_url, board_post_id, controller )
{
  if(confirm("<?php _e('Are you sure you want to delete this post?', 'mingle'); ?>"))
  {
    jQuery.ajax( {
      type: "POST",
      url: mingle_url,
      data: "controller=" + controller + "&action=delete_post&board_post_id=" + board_post_id,
      success: function(html) {
        jQuery('#mngl-board-comment-list-' + board_post_id).slideUp();
        jQuery('.mngl-board-post-' + board_post_id).slideUp();
      }
    });
  }
}

function mngl_delete_board_comment( mingle_url, board_comment_id, controller )
{
  if(confirm("<?php _e('Are you sure you want to delete this comment?', 'mingle'); ?>"))
  {
    jQuery.ajax( {
      type: "POST",
      url: mingle_url,
      data: "controller=" + controller + "&action=delete_comment&board_comment_id=" + board_comment_id,
      success: function(html) {
        jQuery('#mngl-board-comment-' + board_comment_id).slideUp();
      }
    });
  }
}

function mngl_toggle_comment_form( update_id )
{
  jQuery('#mngl-board-comment-list-' + update_id).show();
  jQuery('#mngl-comment-form-' + update_id).toggle();
  jQuery('#mngl-fake-board-comment-' + update_id).toggle();
  jQuery('#mngl-board-comment-input-' + update_id).focus();
}

function mngl_show_board_post_form()
{
  jQuery('#mngl-fake-board-post-form').toggle();
  jQuery('#mngl-board-post-form').toggle();
  jQuery('#mngl-board-post-input').focus();
}

function mngl_toggle_hidden_comments(board_post_id)
{
  jQuery('.mngl-hidden-comment-'+board_post_id).show();
  jQuery('#mngl-show-hidden-comments-'+board_post_id).hide();
}

function mngl_delete_friend( mingle_url, user_id, friend_id )
{
  if(confirm("<?php _e('Are you sure you want to delete this friend?', 'mingle'); ?>"))
  {
    jQuery.ajax( {
      type: "POST",
      url: mingle_url,
      data: "controller=friends&action=delete_friend&user_id=" + user_id + "&friend_id=" + friend_id,
      success: function(html) {
        jQuery('#mngl-friend-'+friend_id).slideUp();
      }
    });
  }
}

function mngl_accept_friend_request( mingle_url, request_id, requestor_name )
{
  mngl_replace_id_with_loading_indicator('request-' + request_id);
  jQuery.ajax( {
    type: "POST",
    url: mingle_url,
    data: "controller=friends&action=accept_friend&request_id=" + request_id,
    success: function(html) {
      jQuery( '#request-' + request_id ).replaceWith( 'You\'re now friends with ' + requestor_name );
    }
  });
}

function mngl_ignore_friend_request( mingle_url, request_id )
{
  mngl_replace_id_with_loading_indicator('request-' + request_id);
  jQuery.ajax( {
    type: "POST",
    url: mingle_url,
    data: "controller=friends&action=ignore_friend&request_id=" + request_id,
    success: function(html) {
      jQuery( '#request-' + request_id ).slideUp();
    }
  });
}

function mngl_search_directory( search_query )
{
  mngl_replace_id_with_loading_indicator('mngl-profile-results');
  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "&controller=profile&action=search&sq=" + search_query,
    success: function(html) {
      jQuery( '#mngl-profile-results' ).replaceWith(html);
      if( search_query != '' )
      {
        jQuery( '.mngl-search-reset-button' ).show();
      }
      else
      {
        jQuery( '.mngl-search-reset-button' ).hide();
      }
    }
  });
}

function mngl_search_friends( search_query, page_params )
{
  mngl_replace_id_with_loading_indicator('mngl-friends-directory');
  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "&controller=friends&action=search&sq=" + search_query + page_params,
    success: function(html) {
      jQuery( '#mngl-friends-directory' ).replaceWith(html);
    }
  });
}
function mngl_delete_profile_avatar( mingle_url, user_id )
{
  if(confirm("<?php _e('Are you sure you want to delete your avatar?', 'mingle'); ?>"))
  {
    jQuery.ajax( {
      type: "POST",
      url: mingle_url,
      data: "controller=profile&action=delete_avatar&user_id=" + user_id,
      success: function(html) {
        jQuery('#mngl-avatar-edit-display').replaceWith(html);
      }
    });
  }
}

function mngl_toggle_two_ids( first_id, second_id )
{
  jQuery(first_id).toggle();
  jQuery(second_id).toggle();
}

function mngl_show_search_form()
{
  jQuery('#mngl-fake-search-form').hide();
  jQuery('#mngl-search-form').show();
  jQuery('#mngl-search-input').focus();
}

function mngl_remove_tag( html_tag )
{
  jQuery( html_tag ).remove();
}

function mngl_add_default_user()
{
  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "controller=options&action=add_default_user",
    success: function(html) {
      jQuery('.mngl-default-friends-table').append(html);
    }
  });
}

function mngl_replace_id_with_loading_indicator(tagname)
{
  jQuery('#'+tagname).replaceWith('<img id="' + tagname + '" src="<?php echo MNGL_IMAGES_URL; ?>/ajax-loader.gif" alt="<?php _e('Loading...', 'mingle'); ?>" />');
}

function mngl_replace_class_with_loading_indicator(tagname)
{
  jQuery('.'+tagname).replaceWith('<img class="' + tagname + '" src="<?php echo MNGL_IMAGES_URL; ?>/ajax-loader.gif" alt="<?php _e('Loading...', 'mingle'); ?>" />');
}

function mngl_load_growables()
{
  jQuery(".mngl-growable-hidden").show();
  jQuery(".mngl-growable").elastic();
  jQuery(".mngl-growable-hidden").hide();
}

function mngl_show_tooltip( tooltip_content, tooltip_element )
{
  jQuery(tooltip_element).qtip({
    content: tooltip_content
  });
}

function mngl_set_active_tab( tab )
{
  jQuery('#mngl-profile-tab-control li').removeClass('mngl-active-profile-tab');
  jQuery('#mngl-' + tab + '-tab-button').addClass('mngl-active-profile-tab');
  jQuery('.mngl-profile-tab').hide();
  jQuery('#mngl-' + tab + '-tab').show();
}

function mngl_mailer_options()
{
  if( jQuery('#mngl_mailer-type').val() == 'smtp' )
  {
    jQuery('#mngl-sendmail-form').slideUp( 'normal', function() {
      jQuery('#mngl-smtp-form').slideDown();
    } );
  }
  else if( jQuery('#mngl_mailer-type').val() == 'sendmail' )
  {
    jQuery('#mngl-smtp-form').slideUp( 'normal', function() {
      jQuery('#mngl-sendmail-form').slideDown();
    } );
  }
  else
  {
    jQuery('#mngl-sendmail-form').slideUp();
    jQuery('#mngl-smtp-form').slideUp();
  }
}

function mngl_center_image( curr_obj )
{
  var obj_height = jQuery( curr_obj ).height();
  var img_height = jQuery( curr_obj ).find('img').height();
  
  var img_tb_margin = (obj_height - img_height) / 2;
  
  //alert( "obj height: " + obj_height + " img height: " + img_height + " img_tb_margin " + img_tb_margin );
  
  jQuery( curr_obj ).find('img').css('margin-top', img_tb_margin);
  jQuery( curr_obj ).find('img').css('margin-bottom', img_tb_margin);
}

function mngl_add_field( field_index )
{
  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "controller=options&action=add_custom_field&index=" + field_index,
    success: function(html) {
      jQuery('#mngl-add-button').replaceWith(html);
    }
  });
}

function mngl_add_field_option( field_index, option_index )
{
  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "controller=options&action=add_custom_field_option&field_index=" + field_index + "&option_index=" + option_index,
    success: function(html) {
      jQuery('#mngl-add-option-button-' + field_index).replaceWith(html);
    }
  });
}

function mngl_show_field_options( field_index, type )
{
  if(type == 'dropdown')
  {
    jQuery('#mngl_field_options_wrapper_' + field_index).show();
  }
  else
  {
    jQuery('#mngl_field_options_wrapper_' + field_index).hide();
  }
}

function mngl_reply_to_message( thread_id, message )
{
  jQuery('#mngl_reply_button').toggle();
  jQuery('#mngl_reply_loading').toggle();
  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "controller=messages&action=mngl_process_reply_form&mngl_thread_id=" + thread_id + "&mngl_reply=" + mngl_escape(message),
    success: function(html) {
      jQuery('#mngl_messages_table').append(html);
      jQuery('#mngl_reply').val(''); // clear the textarea
      jQuery('#mngl_reply').elastic();
      jQuery('#mngl_reply_button').toggle();
      jQuery('#mngl_reply_loading').toggle();
    }
  });
}

function mngl_delete_thread( thread_id )
{
  if(confirm('<?php _e('Are you sure you want to delete this message?', 'mingle'); ?>'))
  {
    jQuery.ajax( {
      type: "POST",
      url: '<?php echo MNGL_SCRIPT_URL; ?>',
      data: "controller=messages&action=delete_thread&t=" + thread_id,
      success: function(html) {
        jQuery('#mngl_thread_' + thread_id).fadeOut('slow');
      }
    });
  }
}

function mngl_bulk_action()
{
  var action = jQuery('#mngl_message_actions').val();
  
  if(action == 'delete_threads')
  {
    if(!confirm('<?php _e('Are you sure you want to delete these messages?', 'mingle'); ?>'))
    {
      return;
    }
  }

  var thread_ids = jQuery(".mngl_message_checkbox:checked").map(function(){
                     return jQuery(this).val();
                   }).get();

  jQuery.ajax( {
    type: "POST",
    url: '<?php echo MNGL_SCRIPT_URL; ?>',
    data: "controller=messages&action=" + action + "&ts=" + thread_ids.join(","),
    success: function(html) {
      if(action=='delete_threads')
      {
        jQuery('.mngl_message_checkbox:checked').parent().parent().fadeOut('slow');
      }
      else if(action=='mark_unread')
      {
        jQuery('.mngl_message_checkbox:checked').parent().parent().children().css('background-color','lightgray');
      }
      else if(action=='mark_read')
      {
        jQuery('.mngl_message_checkbox:checked').parent().parent().children().css('background-color','white');
      }
      
      jQuery('.mngl_message_checkbox:checked').removeAttr('checked');
    }
  });
}

function mngl_toggle_message_composer()
{
  jQuery('#mngl_message_composer').slideToggle();
}

jQuery(document).ready(function() {
  mngl_load_growables();

<?php // translators: Please don't translate this string ... You can see what this date format means here http://docs.jquery.com/UI/Datepicker/formatDate ... try to select a relevant format for those using Mingle in your language.
  $date_format = __('MM d, yy', 'mingle');
  
  // translators: Please don't translate this string ... This option indicates whether the first day on the calendar is a Sunday or Monday -- Sunday is represented as a '0' and Monday is represented as a '1'
  $first_day = __('0', 'mingle');
  
  $month_names = "['" . __('January', 'mingle') . "','" . __('February', 'mingle') . "','" . __('March', 'mingle') . "','" . __('April', 'mingle') . "','" . __('May', 'mingle') . "','" . __('June', 'mingle') . "','" . __('July', 'mingle') . "','" . __('August', 'mingle') . "','" . __('September', 'mingle') . "','" . __('October', 'mingle') . "','" . __('November', 'mingle') . "','" . __('December', 'mingle') . "']";
  
  // translators: This is the short version of the month name...
  $short_month_names = "['" . __('Jan', 'mingle') . "','" . __('Feb', 'mingle') . "','" . __('Mar', 'mingle') . "','" . __('Apr', 'mingle') . "','" . __('May', 'mingle') . "','" . __('Jun', 'mingle') . "','" . __('Jul', 'mingle') . "','" . __('Aug', 'mingle') . "','" . __('Sept', 'mingle') . "','" . __('Oct', 'mingle') . "','" . __('Nov', 'mingle') . "','" . __('Dec', 'mingle') . "']";
  
  $day_names = "['" . __('Sunday', 'mingle') . "','" . __('Monday', 'mingle') . "','" . __('Tuesday', 'mingle') . "','" . __('Wednesday', 'mingle') . "','" . __('Thursday', 'mingle') . "','" . __('Friday', 'mingle') . "','" . __('Saturday', 'mingle') . "']";
  
  // translators: This is the short version of the day name...
  $short_day_names = "['" . __('Sun', 'mingle') . "','" . __('Mon', 'mingle') . "','" . __('Tue', 'mingle') . "','" . __('Wed', 'mingle') . "','" . __('Thu', 'mingle') . "','" . __('Fri', 'mingle') . "','" . __('Sat', 'mingle') . "']";
  
  // translators: This is the minimized short version of the day name
  $min_day_names = "['" . __('Su', 'mingle') . "','" . __('Mo', 'mingle') . "','" . __('Tu', 'mingle') . "','" . __('We', 'mingle') . "','" . __('Th', 'mingle') . "','" . __('Fr', 'mingle') . "','" . __('Sa', 'mingle') . "']";
  
  // translators: Please don't translate this string ... Just set it to 'isRTL: true' if your language is drawn from Right-to-Left
  $rtl = __('isRTL: false', 'mingle');
  
  $datepicker_options = "dateFormat: '{$date_format}', changeMonth: true, changeYear: true, firstDay: {$first_day}, monthNames: {$month_names}, monthNamesShort: {$short_month_names}, dayNames: {$day_names}, dayNamesShort: {$short_day_names}, dayNamesMin: {$min_day_names}, {$rtl}, minDate: '-100y', maxDate: '+5y', yearRange: '-100y:+5y'";
?>

  jQuery(".mngl-datepicker").datepicker({ <?php echo $datepicker_options; ?> });
  
  // By suppling no content attribute, the library uses each elements title attribute by default

  jQuery('.mngl-grid-cell a').each(function()
  {
    jQuery(this).qtip({
      content: {
        text: jQuery(this).parent().attr('rel')
      },
      position: {
        corner: {
          target: 'bottomMiddle',
          tooltip: 'topMiddle'
        }
      },
      style: {
        border: {
            width: 5,
            radius: 5
        },
        padding: 5, 
        textAlign: 'center',
        tip: true
      }
    });
  });
});
