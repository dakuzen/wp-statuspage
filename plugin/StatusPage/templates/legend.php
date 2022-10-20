<?php

// Open Block
?>
<div class="<?php echo $app->getConfig('app-slug') ?>-app statuspage-legend">
  <ul class="statuspage-legend-list">
    <li class="statuspage-status operational" data-tooltip>
      <i class="fa fa-check"></i> <?php _e('Operational', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Operational</strong><br>The service is functioning as expected and in a timely manner.', 'statuspage'); ?></div>
    </li>
    <li class="statuspage-status degraded-performance" data-tooltip>
      <i class="fa fa-minus-square"></i> <?php _e('Degraded Performance', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Degraded Performance</strong><br>Degraded performance means the service is working but is slow or otherwise impacted in a minor way. An example of this would be if you were experiencing an unusually high amount of traffic and the latency is higher than normal.', 'statuspage'); ?></div>
    </li>
    <li class="statuspage-status parial-outage" data-tooltip>
      <i class="fa fa-exclamation-triangle"></i> <?php _e('Partial Outage', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Partial Outage</strong><br>The service is completely broken for a subset of customers while working as normal for other customers.', 'statuspage'); ?></div>
    </li>
    <li class="statuspage-status major-outage" data-tooltip>
      <i class="fa fa-times"></i> <?php _e('Major Outage', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Major Outage</strong><br>Service is completely unavailable to all customers.', 'statuspage'); ?></div>
    </li>
    <li class="statuspage-status maintenance" data-tooltip>
      <i class="fa fa-wrench"></i> <?php _e('Maintenance', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Under Maintenance</strong><br>The service is currently being worked on.', 'statuspage'); ?></div>
    </li>
  </ul>
</div>
<?php
