<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
use WebuddhaInc\StatusPage\Subscriber as StatusPageSubscriber;

$app = StatusPageApp::getInstance();

// Action
if (in_array(@$_REQUEST['action'], array('validate', 'unsubscribe')) && !empty($_REQUEST['cid'])) {
  $cid = array_filter((array)$_REQUEST['cid'], 'intval');
  if ($cid) {
    foreach( $cid AS $subscriber_id ){
      $subscribers = $wpdb->get_results( $wpdb->prepare("
        SELECT *
        FROM `{$wpdb->prefix}statuspage_subscriptions`
        WHERE `subscriber_id` = %s
        ", $subscriber_id) );
      if ($subscribers)
        if ($_REQUEST['action'] == 'validate')
          StatusPageSubscriber::validate($subscribers[0]->subscriber_validation_key);
        else if ($_REQUEST['action'] == 'unsubscribe')
          StatusPageSubscriber::unsubscribe(null, $subscribers[0]->subscriber_validation_key);
    }
  }
}

// List of Event Logs
$limit = 25;
$paged = (int)(@$_REQUEST['paged'] ? $_REQUEST['paged'] : 1);
$order = (@$_REQUEST['order'] ? $_REQUEST['order'] : null);
$order = in_array($order, array('asc', 'desc')) ? $order : 'desc';
$orderby = (@$_REQUEST['orderby'] ? $_REQUEST['orderby'] : null);
$orderby = in_array($orderby, array('subscriber_email', 'subscriber_date', 'subscriber_verified')) ? $orderby : 'subscriber_date';
$total = $wpdb->get_var("
  SELECT count(*)
  FROM `{$wpdb->prefix}statuspage_subscriptions`
  ");
$pages = ceil($total / $limit);
if ($pages < 1)
  $pages = 1;
if ($paged < 1)
  $paged = 1;
else if ($paged > $pages)
  $paged = $pages;
$subscribers = $wpdb->get_results( $wpdb->prepare("
  SELECT *
  FROM `{$wpdb->prefix}statuspage_subscriptions`
  ORDER BY `{$orderby}` {$order}
  LIMIT %d, %d
  ", ($page * $limit), $limit) );

// View
$app->loadView('admin/subscribers.php', get_defined_vars());