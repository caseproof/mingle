<div id="mngl_message_composer" class="mngl_gray_box">
<form action="" enctype="multipart/form-data" method="post" autocomplete="off">
  <input type="hidden" name="action" id="action" value="mngl_process_composer_form" />
  <input type="hidden" name="<?php echo $mngl_user->id_str; ?>" id="<?php echo $mngl_user->id_str; ?>" value="<?php echo $mngl_user->id; ?>" />
  <input type="hidden" name="<?php echo $mngl_user->screenname_str; ?>" id="<?php echo $mngl_user->screenname_str; ?>" value="<?php echo $mngl_user->screenname; ?>" />
  <table width="100%" class="profile-edit-table">
    <tr>
      <td valign="top" width="100px"><?php _e('Recipients', 'mingle'); ?>:</td>
      <td valign="top">
				<input type="text" name="mngl_message_recipients" id="mngl_message_recipients" class="mngl-profile-edit-field" value="<?php echo $to; ?>" />
      </td>
    </tr>
    <tr>
      <td valign="top"><?php _e('Subject', 'mingle'); ?>:</td>
      <td valign="top"><input type="input" name="mngl_message_subject" id="mngl_message_subject" value="<?php echo MnglAppHelper::format_text($_POST['mngl_message_subject']); ?>" class="mngl-profile-edit-field" /></td>
    </tr>
    <tr>
      <td valign="top"><?php _e('Message', 'mingle'); ?>:</td>
      <td valign="top"><textarea name="mngl_message_body" id="mngl_message_body" class="mngl-profile-edit-field mngl-growable"><?php echo MnglAppHelper::format_text($_POST['mngl_message_body']); ?></textarea></td>
    </tr>
  </table>
  <input type="submit" class="mngl-share-button" name="Update" value="<?php _e('Send', 'mingle'); ?>" />
</form>
</div>

<script type="text/javascript">
jQuery(function() {
	function split(val) {
		return val.split(/,\s*/);
	}

	function extractLast(term) {
		return split(term).pop();
	}
	
	jQuery("#mngl_message_recipients").autocomplete({
		source: function(request, response) {
			jQuery.getJSON("<?php echo MNGL_SCRIPT_URL . "&controller=messages&action=lookup_friends"; ?>", {
				sq: extractLast(request.term)
			}, response);
		},
		search: function() {
			// custom minLength
			var term = extractLast(this.value);
			if (term.length < 2) {
				return false;
			}
		},
		focus: function() {
			// prevent value inserted on focus
			return false;
		},
		select: function(event, ui) {
			var terms = split( this.value );
			// remove the current input
			terms.pop();
			// add the selected item
			terms.push( ui.item.value );
			// add placeholder to get the comma-and-space at the end
			terms.push("");
			this.value = terms.join(", ");
			return false;
		}
	});
	
	<?php echo (((isset($_POST['action']) and ($_POST['action'] == 'mngl_process_composer_form')) or (isset($_GET['mu']) and !empty($_GET['mu'])))?'':"jQuery('#mngl_message_composer').hide();"); ?>
});
</script>
