<?php $col_width = (int)((float)$cols/100.00); ?>
<div class="mngl-user-grid">
  <p class="mngl-user-grid-header"><?php echo number_format( (float)$user_count ); ?> <?php echo $user_type; ?> - <a href="<?php echo $all_users_url; ?>"><?php _e('Show All', 'mingle'); ?></a></p>
  <table width="100%" class="mngl-user-grid-table">
    <?php
      for ($i=0; $i < $rows; $i++) { 
        ?>
          <tr>
            <?php
            for ($j=0; $j < $cols; $j++) { 
              $user_index = ($i * $cols) + $j;
              
              if($user_index >= $user_count)
                break;

              $user = $users[$user_index];
              $avatar = $user->get_avatar(50);
              ?>
                <td width="50px" style="max-width: 50px;" valign="top"><center><div class="mngl-grid-cell" rel="<strong><?php echo $user->screenname; ?></strong><br/><?php echo $user->full_name; ?>"><?php echo $avatar; ?></div></center></td>
              <?php
            }
            ?>
          </tr>
        <?php
      }
    ?>
  </table>
</div>
