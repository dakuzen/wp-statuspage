<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
$app = StatusPageApp::getInstance();

// Load Configuration
$scanConfig = $app->getScanConfig();

// Open Block
?><div class="<?php echo $app->getConfig('app-slug') ?>-app statuspage-statuslist"><?php

// Loop Services
foreach ($scanConfig->services AS $service) {

  // Save Status
  $lastStatus = $app->getPluginOption('serviceHistory_'.$service->componentId);
  $lastStatusCode = str_replace(' ', '-', strtolower($lastStatus->status));

  ?>
  <div class="statuspage-service <?php echo esc_attr($lastStatusCode) ?>">
    <div class="statuspage-service-label"><?php echo esc_html($service->label) ?></div>
    <div class="statuspage-service-status" data-tooltip><?php
      switch(strtolower($lastStatusCode)) {
        case 'operational':
          echo '<i class="fa fa-check"></i>' . esc_html__($lastStatus->status, 'statuspage');
          echo '<div class="tooltip hidden">' . __('<strong>Operational</strong><br>Operational means exactly what it sounds like. The component is functioning as expected and in a timely manner.', 'statuspage') . '</div>';
          break;
        case 'degraded-performance':
          echo '<i class="fa fa-minus-square"></i>' . esc_html__($lastStatus->status, 'statuspage');
          echo '<div class="tooltip hidden">' . __('<strong>Degraded Performance</strong><br>Degraded performance means the component is working but is slow or otherwise impacted in a minor way.<br><br>An example of this would be if you were experiencing an unusually high amount of traffic and the component was taking longer to perform its job than normal.', 'statuspage') . '</div>';
          break;
        case 'partial-outage':
          echo '<i class="fa fa-exclamation-triangle"></i>' . esc_html__($lastStatus->status, 'statuspage');
          echo '<div class="tooltip hidden">' . __('<strong>Partial Outage</strong><br>Components should be set to partial outage when they are completely broken for a subset of customers. An example of this would be if some subset of customer\'s data lived in a specific data center that was down. The component might be broken for that subset of customers but is working for the rest and thus there is a partial outage.', 'statuspage') . '</div>';
          break;
        case 'major-outage':
          echo '<i class="fa fa-times"></i>' . esc_html__($lastStatus->status, 'statuspage');
          echo '<div class="tooltip hidden">' . __('<strong>Major Outage</strong><br>Components should be set to major outage when they are completely unavailable.', 'statuspage') . '</div>';
          break;
        case 'maintenance':
        case 'under-maintenance':
          echo '<i class="fa fa-wrench"></i>' . esc_html__($lastStatus->status, 'statuspage');
          echo '<div class="tooltip hidden">' . __('<strong>Under Maintenance</strong><br>Under maintenance means exactly what it sounds like. The component is currently being worked on.', 'statuspage') . '</div>';
          break;
      }
      ?></div>
  </div>
  <?php
}

// Close Block
?></div><?php

