<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
$app = StatusPageApp::getInstance();

// View
$app->loadView('status.php', get_defined_vars());