<?php
if(class_exists('WP_Widget'))
{
  class MnglLoginWidget extends WP_Widget {
    /** constructor */
    function MnglLoginWidget() {
      parent::WP_Widget(false, $name = __('Mingle Login', 'mingle'));	
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {
      global $mngl_options, $mngl_blogurl, $mngl_blogname, $mngl_friend;	
      extract( $args );
      $title = apply_filters('widget_title', $instance['title']);

      if(!empty($mngl_options->login_page_id) and $mngl_options->login_page_id > 0)
      {
        $login_url = get_permalink($mngl_options->login_page_id);
        $login_delim = MnglAppController::get_param_delimiter_char($login_url);
        $forgot_password_url = "{$login_url}{$login_delim}action=forgot_password";
      }
      else
      {
        $login_url = "{$mngl_blogurl}/wp-login.php";
        $forgot_password_url = "{$mngl_blogurl}/wp-login.php?action=lostpassword";
      }

      if(!empty($mngl_options->signup_page_id) and $mngl_options->signup_page_id > 0)
        $signup_url = get_permalink($mngl_options->signup_page_id);
      else
        $signup_url = $mngl_blogurl . '/wp-login.php?action=register';
      ?>
        <?php echo $before_widget; ?>
        <?php if ( $title )
          echo $before_title . $title . $after_title; ?>
        <?php require( MNGL_VIEWS_PATH . '/shared/login_widget.php' ); ?>
        <?php echo $after_widget; ?>
      <?php
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
      return $new_instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
      $title = esc_attr($instance['title']);
      ?>
        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title', 'mingle'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
      <?php 
    }
  }
}
?>
