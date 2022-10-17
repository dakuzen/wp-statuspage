<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
$app = StatusPageApp::getInstance();

// Load Configuration
$scanConfig = $app->getScanConfig();

// Load Posts
$query = new WP_Query(array(
  'post_type' => 'statuspage_incident',
  'post_status' => 'archive'
  ));
if (empty($query->posts))
  return;
$incidentPosts = $query->posts;

// View
$app->loadView('archive.php', get_defined_vars());