<?php

namespace WebuddhaInc\StatusPage;

use WebuddhaInc\StatusPage\App as StatusPageApp;

class Notification {

  public static function processNotificationStack( $allServiceNotifications ){

    // inspect('Process Notifications', $allServiceNotifications);

    // Stage
    $app = StatusPageApp::getInstance();
    $scanConfig = $app->getScanConfig();

    // Stack Notifications
    $serviceNotifications = array();
    foreach ($scanConfig->services AS $service) {
      foreach ($allServiceNotifications AS $serviceNotification) {
        if ($service->componentId == $serviceNotification->componentId) {
          if (empty($serviceNotifications[$service->componentId]))
          $serviceNotifications[$service->componentId] = (object)array(
            'service' => $service,
            'status'  => null,
            'updates' => array()
            );
          if (!empty($serviceNotification->oldStatus))
            $serviceNotifications[$service->componentId]->status = $serviceNotification;
          else if ($serviceNotification->incidentUpdate )
            $serviceNotifications[$service->componentId]->updates[] = $serviceNotification->incidentUpdate;
          // else wtf mate
        }
      }
    }

    foreach ($serviceNotifications AS $componentId => $componentNotification) {

      // Status Change
      if (!empty($componentNotification->status)) {
        $tmpl = get_post( $app->getPluginOption('emailTmplId_serviceEvent') );
        if ($tmpl) {
          $data = array(
            'service_name' => $componentNotification->service->label,
            'service_status' => $componentNotification->status->newStatus,
            'service_previous_status' => $componentNotification->status->oldStatus
          );
          $subject = $app->interpolateContent($tmpl->post_title, $data);
          $message = $app->interpolateContent($tmpl->post_content, $data);
          $message = str_replace("\n", "<br>", $message);
          if ($componentNotification->updates) {
            $incident_updates = array();
            foreach ($componentNotification->updates AS $incidentUpdate) {
              $incident_updates[] = '<div class="statuspage-incident">';
              $incident_updates[] = '<h3>' . esc_html($incidentUpdate->updateType) . '</h3>';
              $incident_updates[] = '<p><em>' . esc_html(get_date_from_gmt($incidentUpdate->updateCreatedGMT, 'F jS, Y - g:ia')) . '</em><br>' . esc_html($incidentUpdate->updateContent) . '</p>';
              $incident_updates[] = '</div>';
            }
            $message = $app->interpolateContent($message, array('incident_updates' => implode('', $incident_updates)));
          }
          else {
            $message = $app->interpolateContent($message, array('incident_updates' => ''));
          }
          self::queueToSubscribers($subject, $message);
        }
      }

      // Updates Only
      else if (!empty($componentNotification->updates)) {
        $tmpl = get_post( $app->getPluginOption('emailTmplId_serviceUpdate') );
        $serviceHistory = $app->getPluginOption('serviceHistory_'.$componentId);
        if ($tmpl) {
          $data = array(
            'service_name' => $componentNotification->service->label,
            'service_status' => $serviceHistory->status
          );
          $subject = $app->interpolateContent($tmpl->post_title, $data);
          $message = $app->interpolateContent($tmpl->post_content, $data);
          $message = str_replace("\n", "<br>", $message);
          $incident_updates = array();
          foreach ($componentNotification->updates AS $incidentUpdate) {
            $incident_updates[] = '<div class="statuspage-incident">';
            $incident_updates[] = '<h3>' . esc_html($incidentUpdate->updateType) . '</h3>';
            $incident_updates[] = '<p><em>' . esc_html(get_date_from_gmt($incidentUpdate->updateCreatedGMT, 'F jS, Y - g:ia')) . '</em><br>' . esc_html($incidentUpdate->updateContent) . '</p>';
            $incident_updates[] = '</div>';
          }
          $message = $app->interpolateContent($message, array('incident_updates' => implode('', $incident_updates)));
          self::queueToSubscribers($subject, $message);
        }
      }

      else {

        $app->postActivityLog('Invalid Notification:' . json_encode($componentNotification));

      }

    }
  }

  public static function queueToSubscribers( $subject, $message ){
    global $wpdb;
    $app = StatusPageApp::getInstance();
    $limit = 100;
    $limit_start = 0;
    $subscriber_count = $wpdb->get_var("
      SELECT count(*)
      FROM `{$wpdb->prefix}statuspage_subscriptions`
      WHERE `subscriber_validated` = 1
      ");
    $app->postActivityLog('Email ' . $subscriber_count . ' message ' . $subject);
    do {
      $subscribers = $wpdb->get_results( $wpdb->prepare("
        SELECT *
        FROM `{$wpdb->prefix}statuspage_subscriptions`
        WHERE `subscriber_validated` = 1
        LIMIT %d, %d
        ", $limit_start, $limit) );
      if ($subscribers) {
        foreach ($subscribers AS $subscriber) {
          $emailMessage = $app->interpolateContent($message, array(
            'unsubscribe_url' => $app->getRouteUrl('unsubscribe?key=' . $subscriber->subscriber_validation_key),
            'unsubscribe_link' => '<a href="'.$app->getRouteUrl('unsubscribe?key=' . $subscriber->subscriber_validation_key).'" title="'.esc_attr__('Unsubscribe Email', 'statuspage').'">'. esc_html__('Unsubscribe Email', 'statuspage') .'</a>'
          ));
          $app->sendMail( array($subscriber->subscriber_email), $subject, $emailMessage );
        }
      }
      $limit_start += $limit;
    } while ($subscribers);
  }

}
