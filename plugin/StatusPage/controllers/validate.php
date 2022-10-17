<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
use WebuddhaInc\StatusPage\Subscriber as StatusPageSubscriber;

$app = StatusPageApp::getInstance();

if (!empty($_REQUEST['key'])) {
  $response = array(
    'code' => 200,
    'message' => ''
  );
  $result = StatusPageSubscriber::validate($_REQUEST['key']);
  if ($result > 1) {
    $response['code'] = 200;
    $response['message'] = __('<strong>Previously Validated.</strong> Your email was already verified and setup to receive status update alerts.', 'statuspage');
  }
  else if ($result) {
    $response['code'] = 200;
    $response['message'] = __('<strong>Validation Success.</strong> Your email has been verified and you will now receive status update alerts.', 'statuspage');
  }
  else {
    $response['code'] = 500;
    $response['message'] = __('<strong>Validation Error.</strong> The validation key provided is invalid.', 'statuspage');
  }

  // Add for next page
  $app->addSessionMessage($response);

}

// Redirect
wp_redirect(get_permalink($app->getPluginOption('pageId_statusPage')));
