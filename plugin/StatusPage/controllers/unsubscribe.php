<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
use WebuddhaInc\StatusPage\Subscriber as StatusPageSubscriber;

$app = StatusPageApp::getInstance();

if (!empty($_REQUEST['key'])) {
  $response = array(
    'code' => 200,
    'message' => ''
  );
  $result = StatusPageSubscriber::unsubscribe(null, $_REQUEST['key']);
  if ($result) {
    $response['code'] = 200;
    $response['message'] = __('<strong>Unsubscribe Success.</strong> Your email has been unsubscribe and you will no longer receive status update alerts.', 'statuspage');
  }
  else {
    $response['code'] = 500;
    $response['message'] = __('<strong>Unsubscribe Error.</strong> The unsubscribe key provided is invalid or has already been processed.', 'statuspage');
  }

  // Add for next page
  $app->addSessionMessage($response);

}

// Redirect
wp_redirect(get_permalink($app->getPluginOption('pageId_statusPage')));
