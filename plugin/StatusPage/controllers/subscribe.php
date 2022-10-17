<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
use WebuddhaInc\StatusPage\Subscriber as StatusPageSubscriber;

$app = StatusPageApp::getInstance();

// Subscribe
if (!empty($_POST['subscribeEmail'])) {
  $response = array(
    'code' => 200,
    'message' => ''
  );
  $email = filter_var(strtolower($_POST['subscribeEmail']), FILTER_VALIDATE_EMAIL);
  if (!wp_verify_nonce($_POST['_wpnonce'], 'statuspage_subscribe')) {
    $response['code'] = 500;
    $response['message'] = __('<strong>Subscription Error.</strong> Your request verification failed.', 'statuspage');
  }
  else if (!is_email($email)) {
    $response['code'] = 400;
    $response['message'] = __('<strong>Subscription Error.</strong> The email provided is not a valid format.', 'statuspage');
  }
  else {
    if ($subscriber = StatusPageSubscriber::findSubscriber($email)) {
      if (!$subscriber->subscriber_validated) {
        StatusPageSubscriber::sendValidation($email);
        $response['code'] = 205;
        $response['message'] = __('<strong>Subscription Success!</strong> A validation email has been sent again to the address provided. Please check your email to complete the validation step.', 'statuspage');
      }
      else {
        $response['code'] = 201;
        $response['message'] = __('<strong>Subscription Success!</strong> The email provided is already registered for notifications.', 'statuspage');
      }
    }
    else {
      if (StatusPageSubscriber::subscribe($email)) {
        StatusPageSubscriber::sendValidation($email);
        $response['code'] = 202;
        $response['message'] = __('<strong>Subscription Success!</strong> The email provided has been registered for notifications. Please check your email to complete the validation step.', 'statuspage');
      }
      else {
        $response['code'] = 500;
        $response['message'] = __('<strong>Error Processing Subscription!</strong> We encountered an error processing your request. Please try again later.', 'statuspage');
      }
    }
  }
  $app->loadView('subscribe-response.php', get_defined_vars());
  exit;
}

// Load Configuration
$scanConfig = $app->getScanConfig();

// View
$app->loadView('subscribe.php', get_defined_vars());