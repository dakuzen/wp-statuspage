<?php

use WebuddhaInc\StatusPage\App as StatusPageApp;
use WebuddhaInc\StatusPage\Subscriber as StatusPageSubscriber;

$app = StatusPageApp::getInstance();

// Action
if ((@$_REQUEST['action'] == 'delete') && !empty($_REQUEST['cid'])) {
  $cid = array_filter((array)$_REQUEST['cid'], 'intval');
  if ($cid)
    $wpdb->query("DELETE FROM `{$wpdb->prefix}statuspage_logs` WHERE `log_id` IN (". implode(',', $cid) .")");
}

// List of Event Logs
$limit = 25;
$paged = (int)(@$_REQUEST['paged'] ? $_REQUEST['paged'] : 1);
$order = (@$_REQUEST['order'] ? $_REQUEST['order'] : null);
$order = in_array($order, array('asc', 'desc')) ? $order : 'desc';
$orderby = (@$_REQUEST['orderby'] ? $_REQUEST['orderby'] : null);
$orderby = in_array($orderby, array('log_date')) ? $orderby : 'log_date';
$total = $wpdb->get_var("
  SELECT count(*)
  FROM `{$wpdb->prefix}statuspage_logs`
  ");
$pages = ceil($total / $limit);
if ($pages < 1)
  $pages = 1;
if ($paged < 1)
  $paged = 1;
else if ($paged > $pages)
  $paged = $pages;
$eventlogs = $wpdb->get_results( $wpdb->prepare("
  SELECT *
  FROM `{$wpdb->prefix}statuspage_logs`
  ORDER BY `{$orderby}` {$order}
  LIMIT %d, %d
  ", (($paged - 1) * $limit), $limit) );

// View
$app->loadView('admin/eventlogs.php', get_defined_vars());