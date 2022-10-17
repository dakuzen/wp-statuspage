<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;

$app = StatusPageApp::getInstance();

// Store
if (!empty($_POST[$app->getConfig('app-slug').'_settings'])) {
  $options = $_POST[$app->getConfig('app-slug').'_settings'];
  foreach ($options AS $key => $value) {
    $app->savePluginOption($key, stripslashes($value));
  }
}

// View
$app->loadView('admin/settings.php', get_defined_vars());