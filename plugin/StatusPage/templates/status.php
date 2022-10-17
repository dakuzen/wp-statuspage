<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
$app = StatusPageApp::getInstance();

// Load Configuration
$scanConfig = $app->getScanConfig();

// Open Block
?><div id="<?php echo $app->getConfig('app-slug') ?>-app" class="statuspage-statuslist"><?php

// Loop Services
foreach ($scanConfig->services AS $service) {

  // Save Status
  $lastStatus = $app->getPluginOption('serviceHistory_'.$service->componentId);
  $lastStatusCode = str_replace(' ', '-', strtolower($lastStatus->status));

  ?>
  <div class="statuspage-service <?php echo esc_attr($lastStatusCode) ?>">
    <div class="statuspage-service-label"><?php echo esc_html($service->label) ?></div>
    <div class="statuspage-service-status"><?php
      switch(strtolower($lastStatusCode)) {
        case 'operational':
          echo '<i class="fa fa-check"></i>';
          break;
        case 'degraded-performance':
          echo '<i class="fa fa-minus-square"></i>';
          break;
        case 'partial-outage':
          echo '<i class="fa fa-exclamation-triangle"></i>';
          break;
        case 'major-outage':
          echo '<i class="fa fa-times"></i>';
          break;
        case 'maintenance':
          echo '<i class="fa fa-wrench"></i>';
          break;
      }
      echo esc_html($lastStatus->status);
      ?></div>
  </div>
  <?php
}

// Close Block
?></div><?php

