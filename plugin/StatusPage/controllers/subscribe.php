<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
$app = StatusPageApp::getInstance();

// Load Configuration
$scanConfig = $app->getScanConfig();

// View
$app->loadView('subscribe.php', get_defined_vars());