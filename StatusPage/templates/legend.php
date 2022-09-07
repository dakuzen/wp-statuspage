<?php

// Open Block
?><div id="<?php echo $app->getConfig('app-slug') ?>-app" class="statuspage-legend">
  <ul class="statuspage-legend-list">
    <li class="statuspage-status operational"><i class="fa fa-check"></i> <?php _e('Operational', 'statuspage'); ?></li>
    <li class="statuspage-status degraded-performance"><i class="fa fa-minus-square"></i> <?php _e('Degraded Performance', 'statuspage'); ?></li>
    <li class="statuspage-status parial-outage"><i class="fa fa-exclamation-triangle"></i> <?php _e('Partial Outage', 'statuspage'); ?></li>
    <li class="statuspage-status major-outage"><i class="fa fa-times"></i> <?php _e('Major Outage', 'statuspage'); ?></li>
    <li class="statuspage-status maintenance"><i class="fa fa-wrench"></i> <?php _e('Maintenance', 'statuspage'); ?></li>
  </ul>
</div><?php
