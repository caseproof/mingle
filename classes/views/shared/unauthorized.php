<?php global $mngl_options;
if(!empty($mngl_options->login_page_id) and $mngl_options->login_page_id > 0)
{
  $permalink = get_permalink($mngl_options->login_page_id);
  $delim     = MnglAppController::get_param_delimiter_char($permalink);
  $login_url = $permalink . $delim . 'redirect_to=' . urlencode($_SERVER['REQUEST_URI']);
}
else
  $login_url = wp_login_url($_SERVER['REQUEST_URI']);

if(!empty($mngl_options->signup_page_id) and $mngl_options->signup_page_id > 0)
  $signup_url = get_permalink($mngl_options->signup_page_id);
else
  $signup_url = $mngl_blogurl . '/wp-login.php?action=register';
?>
<p><?php printf(__('You\'re unauthorized to view this page. Why don\'t you %s and try again.', 'mingle'), '<a href="'. $login_url . '">' . __('Login', 'mingle') . '</a>'); ?>
<?php if(get_option('users_can_register')) {
?> <?php _e('Don\'t have a Login?', 'mingle'); ?> <a href="<?php echo $signup_url; ?>"><?php _e('Register for an Account.', 'mingle'); ?></a>
<?php } ?>
