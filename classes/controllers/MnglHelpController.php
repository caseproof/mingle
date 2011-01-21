<?php

class MnglHelpController
{
  function MnglHelpController()
  {
    //add_action('install_plugins_table_header', array($this, 'get_started_message'));
    add_action('after_plugin_row', array(&$this, 'get_started_message'));
    add_action('admin_notices', array(&$this, 'get_started_headline_message'));
  }

  function get_started_message( $plugin )
  {
    global $mngl_options;
    
    if( $plugin == 'mingle/mingle.php' and
        !$mngl_options->setup_complete )
    {
  ?>
    <td colspan="5" class="plugin-update">&nbsp;&nbsp;<?php printf(__('Mingle must be configured. Go to the %1$sadmin page%2$s to setup your new social network!', 'mingle'), '<a href="?page=mingle/mingle.php">', '</a>'); ?></td>
  <?php
    }
  }
  
  function get_started_headline_message()
  {
    global $mngl_options;
    
    if( !$mngl_options->setup_complete )
      require(MNGL_VIEWS_PATH . '/shared/must_configure.php');
  }
}
?>
