<?php

// Open Block
?>
<div class="<?php echo $app->getConfig('app-slug') ?>-app statuspage-legend">
  <ul class="statuspage-legend-list">
    <li class="statuspage-status operational" data-tooltip>
      <i class="fa fa-check"></i> <?php _e('Operational', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Operational</strong><br>Operational means exactly what it sounds like. The component is functioning as expected and in a timely manner.', 'statuspage'); ?></div>
    </li>
    <li class="statuspage-status degraded-performance" data-tooltip>
      <i class="fa fa-minus-square"></i> <?php _e('Degraded Performance', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Degraded Performance</strong><br>Degraded performance means the component is working but is slow or otherwise impacted in a minor way.<br><br>An example of this would be if you were experiencing an unusually high amount of traffic and the component was taking longer to perform its job than normal.', 'statuspage'); ?></div>
    </li>
    <li class="statuspage-status parial-outage" data-tooltip>
      <i class="fa fa-exclamation-triangle"></i> <?php _e('Partial Outage', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Partial Outage</strong><br>Components should be set to partial outage when they are completely broken for a subset of customers. An example of this would be if some subset of customer\'s data lived in a specific data center that was down. The component might be broken for that subset of customers but is working for the rest and thus there is a partial outage.', 'statuspage'); ?></div>
    </li>
    <li class="statuspage-status major-outage" data-tooltip>
      <i class="fa fa-times"></i> <?php _e('Major Outage', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Major Outage</strong><br>Components should be set to major outage when they are completely unavailable.', 'statuspage'); ?></div>
    </li>
    <li class="statuspage-status maintenance" data-tooltip>
      <i class="fa fa-wrench"></i> <?php _e('Maintenance', 'statuspage'); ?>
      <div class="tooltip hidden"><?php _e('<strong>Under Maintenance</strong><br>Under maintenance means exactly what it sounds like. The component is currently being worked on.', 'statuspage'); ?></div>
    </li>
  </ul>
</div>
<?php
